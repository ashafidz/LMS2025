<?php

namespace App\Services;

use App\Models\Course;
use App\Models\KmeansRun;
use App\Models\KmeansClusterAssignment;
use App\Jobs\GenerateClusterLabelsJob;
use App\Models\ProfilingAttempt;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Clusterers\KMeans;
use Rubix\ML\Transformers\NumericStringConverter;
use Exception;

class KMeansService
{
    /**
     * Feature definitions with their domain ranges for Min-Max normalization.
     *
     * Fitur persentase (mastery, performance, autonomy, competence, relatedness)
     * menggunakan range 0-100 karena nilainya adalah contribution_pct.
     *
     * Fitur prior knowledge menggunakan 0-100 (average_score dalam bentuk persentase).
     *
     * Fitur AI Preference (transparency, guidance, adaptivity, feedback)
     * menggunakan range 1-5 (skala Likert).
     *
     * CATATAN: ZScaleStandardizer SENGAJA TIDAK DIGUNAKAN karena fitur-fitur
     * di sini bersifat constrained (mastery+performance = 100%, dst.), sehingga
     * variance-nya secara alami kecil. Z-Score akan mendistorsi perbedaan kecil
     * yang tidak signifikan dan menghasilkan kluster yang tidak bermakna.
     */
    private array $featureRanges = [
        'mastery'      => ['min' => 0,  'max' => 100],
        'performance'  => ['min' => 0,  'max' => 100],
        'knowledge'    => ['min' => 0,  'max' => 100],
        'autonomy'     => ['min' => 0,  'max' => 100],
        'competence'   => ['min' => 0,  'max' => 100],
        'relatedness'  => ['min' => 0,  'max' => 100],
        'transparency' => ['min' => 1,  'max' => 5],
        'guidance'     => ['min' => 1,  'max' => 5],
        'adaptivity'   => ['min' => 1,  'max' => 5],
        'feedback'     => ['min' => 1,  'max' => 5],
    ];

    /**
     * Build feature matrix for a specific course using all 4 components.
     * Features: [mastery%, performance%, knowledge_pct, autonomy%, competence%,
     *            relatedness%, transparency_avg, guidance_avg, adaptivity_avg, feedback_avg]
     * Only includes completed attempts.
     *
     * Returns both the raw matrix (for centroid display) and the normalized matrix
     * (for K-Means computation).
     */
    public function buildFeatureMatrix(Course $course): array
    {
        $attempts = ProfilingAttempt::with([
            'componentScores.dimension'
        ])
            ->where('course_id', $course->id)
            ->where('status', 'completed')
            ->get();

        if ($attempts->count() < 3) {
            throw new Exception("Minimal butuh 3 student yang telah menyelesaikan test untuk menjalankan K-Means.");
        }

        $rawMatrix    = [];
        $normMatrix   = [];
        $attemptIds   = [];

        foreach ($attempts as $attempt) {
            $scores = $attempt->componentScores;

            $raw = [
                'mastery'      => $scores->where('dimension.name', 'Mastery Goal')->first()?->contribution_pct ?? 0,
                'performance'  => $scores->where('dimension.name', 'Performance Goal')->first()?->contribution_pct ?? 0,
                'knowledge'    => $scores->whereNull('dimension_id')->whereNull('component_id')->first()?->average_score ?? 0,
                'autonomy'     => $scores->where('dimension.name', 'Autonomy')->first()?->contribution_pct ?? 0,
                'competence'   => $scores->where('dimension.name', 'Competence')->first()?->contribution_pct ?? 0,
                'relatedness'  => $scores->where('dimension.name', 'Relatedness')->first()?->contribution_pct ?? 0,
                'transparency' => $scores->where('dimension.name', 'Transparency')->first()?->average_score ?? 0,
                'guidance'     => $scores->where('dimension.name', 'Guidance')->first()?->average_score ?? 0,
                'adaptivity'   => $scores->where('dimension.name', 'Adaptivity')->first()?->average_score ?? 0,
                'feedback'     => $scores->where('dimension.name', 'Feedback')->first()?->average_score ?? 0,
            ];

            $rawMatrix[]   = array_values($raw);
            $normMatrix[]  = $this->normalizeRow($raw);
            $attemptIds[]  = $attempt->id;
        }

        return [
            'raw_matrix'  => $rawMatrix,   // Untuk tampilan centroid (nilai asli)
            'norm_matrix' => $normMatrix,  // Untuk komputasi K-Means (nilai 0-1)
            'attempt_ids' => $attemptIds,
        ];
    }

    /**
     * Normalize a single raw feature row using domain-aware Min-Max scaling.
     * Output: semua fitur berada dalam range [0, 1].
     */
    private function normalizeRow(array $rawRow): array
    {
        $normalized = [];
        $featureKeys = array_keys($this->featureRanges);

        foreach ($featureKeys as $key) {
            $val = $rawRow[$key] ?? 0;
            $min = $this->featureRanges[$key]['min'];
            $max = $this->featureRanges[$key]['max'];
            $range = $max - $min;

            $normalized[] = ($range > 0) ? round(($val - $min) / $range, 6) : 0;
        }

        return $normalized;
    }

    /**
     * Find optimal K using Elbow Method (Inertia/Kneedle algorithm).
     * Evaluates K from $kMin to $kMax (max cannot exceed sample size - 1).
     *
     * Menggunakan normalized matrix agar Elbow Method mendeteksi siku yang benar.
     */
    public function findOptimalK(array $normMatrix, int $kMin = 2, int $kMax = 8): array
    {
        $samplesCount = count($normMatrix);

        // K tidak boleh melebihi jumlah sample - 1
        $actualMaxK = min($kMax, $samplesCount - 1);

        // Kalau sample terlalu sedikit, gunakan K=2
        if ($actualMaxK < $kMin) {
            return [
                'optimal_k' => 2,
                'elbow_data' => [2 => 0],
            ];
        }

        $dataset = new Unlabeled($normMatrix);
        $dataset->apply(new NumericStringConverter());

        $elbowData = [];
        $numTrials = 5; // Jalankan 5x per K, ambil rata-rata inertia untuk stabilitas

        for ($k = $kMin; $k <= $actualMaxK; $k++) {
            $totalInertia = 0;
            for ($trial = 0; $trial < $numTrials; $trial++) {
                srand(42 + $trial);
                $estimator = new KMeans($k);
                $estimator->train(clone $dataset);
                $losses = $estimator->losses();
                $totalInertia += (is_array($losses) && count($losses) > 0) ? end($losses) : 0;
            }
            $elbowData[$k] = $totalInertia / $numTrials;
        }

        // Kneedle algorithm: cari titik dengan jarak terbesar ke garis lurus
        // yang menghubungkan titik pertama dan terakhir pada kurva inertia
        $optimalK    = $kMin;
        $maxDistance = -1;

        $x1 = $kMin;       $y1 = $elbowData[$kMin];
        $x2 = $actualMaxK; $y2 = $elbowData[$actualMaxK];
        $denominator = sqrt(pow($y2 - $y1, 2) + pow($x2 - $x1, 2));

        if ($denominator > 0) {
            for ($k = $kMin; $k <= $actualMaxK; $k++) {
                $x0 = $k;
                $y0 = $elbowData[$k];
                $numerator = abs(($y2 - $y1) * $x0 - ($x2 - $x1) * $y0 + $x2 * $y1 - $y2 * $x1);
                $distance  = $numerator / $denominator;

                if ($distance > $maxDistance) {
                    $maxDistance = $distance;
                    $optimalK    = $k;
                }
            }
        }

        return [
            'optimal_k' => $optimalK,
            'elbow_data' => $elbowData,
        ];
    }

    /**
     * Run K-Means with specified K value on the NORMALIZED matrix.
     * Seed deterministik (42) → hasil identik pada data yang sama.
     */
    public function run(array $normMatrix, int $k): array
    {
        $dataset = new Unlabeled($normMatrix);
        $dataset->apply(new NumericStringConverter());

        srand(42);
        $estimator = new KMeans($k);
        $estimator->train($dataset);

        return $estimator->predict($dataset);
    }

    /**
     * Build scatter data for visualization.
     *
     * Menggunakan RAW features untuk tampilan agar nilai yang ditampilkan
     * di chart sesuai dengan data asli siswa (bukan nilai ternormalisasi).
     *
     * X-axis: Mastery Goal %   (raw index 0)
     * Y-axis: Prior Knowledge % (raw index 2)
     *
     * Kluster assignment tetap dari hasil K-Means pada normalized matrix,
     * sehingga konsisten secara komputasi.
     */
    public function buildPcaScatterData(array $rawMatrix, array $predictions, array $attemptIds): array
    {
        $scatterData = [];

        foreach ($rawMatrix as $index => $sample) {
            $scatterData[] = [
                'attempt_id' => $attemptIds[$index],
                'x'          => round($sample[0] ?? 0, 2), // Mastery Goal %
                'y'          => round($sample[2] ?? 0, 2), // Prior Knowledge %
                'cluster'    => $predictions[$index],
            ];
        }

        return $scatterData;
    }

    /**
     * Orchestrate the entire K-Means pipeline for a course.
     */
    public function executeClustering(Course $course): KmeansRun
    {
        $data       = $this->buildFeatureMatrix($course);
        $rawMatrix  = $data['raw_matrix'];
        $normMatrix = $data['norm_matrix'];
        $attemptIds = $data['attempt_ids'];

        // Gunakan normalized matrix untuk Elbow + K-Means
        $optimalData = $this->findOptimalK($normMatrix);
        $optimalK    = $optimalData['optimal_k'];

        $predictions = $this->run($normMatrix, $optimalK);

        // Scatter plot menggunakan raw matrix untuk nilai tampilan yang jujur
        $scatterData = $this->buildPcaScatterData($rawMatrix, $predictions, $attemptIds);
        $optimalData['scatter_data'] = $scatterData;

        $run = KmeansRun::create([
            'course_id'    => $course->id,
            'triggered_by' => auth()->id(),
            'status'       => 'running',
        ]);

        $this->saveResults($run, $optimalData, $attemptIds, $predictions, $scatterData);

        GenerateClusterLabelsJob::dispatch($run)->delay(now()->addSeconds(2));

        return $run;
    }

    /**
     * Save K-Means results and assignments to DB.
     */
    public function saveResults(KmeansRun $run, array $runResults, array $attemptIds, array $predictions, array $scatterData = []): void
    {
        $run->update([
            'status'         => 'completed',
            'k_value'        => $runResults['optimal_k'],
            'result_summary' => $runResults,
        ]);

        KmeansClusterAssignment::where('run_id', $run->id)->delete();

        $assignments = [];
        foreach ($predictions as $index => $clusterNum) {
            $assignments[] = [
                'run_id'         => $run->id,
                'attempt_id'     => $attemptIds[$index],
                'cluster_number' => $clusterNum + 1, // Rubix ML menggunakan 0-indexed
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        KmeansClusterAssignment::insert($assignments);
    }
}

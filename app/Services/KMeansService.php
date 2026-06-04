<?php

namespace App\Services;

use App\Models\Course;
use App\Models\KmeansRun;
use App\Models\KmeansClusterAssignment;
use App\Models\ProfilingAttempt;
use Illuminate\Support\Collection;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Clusterers\KMeans;
use Rubix\ML\Clusterers\Seeders\KMC2;
use Rubix\ML\Transformers\NumericStringConverter;
use Rubix\ML\Transformers\ZScaleStandardizer;
use Rubix\ML\Transformers\PrincipalComponentAnalysis;
use Exception;

class KMeansService
{
    /**
     * Build feature matrix for a specific course using all 4 components.
     * Features: [mastery%, performance%, knowledge_pct, autonomy%, competence%, relatedness%, transparency_avg, guidance_avg, adaptivity_avg, feedback_avg]
     * Only includes completed attempts.
     */
    public function buildFeatureMatrix(Course $course): array
    {
        $attempts = ProfilingAttempt::with([
            'componentScores.dimension'
        ])
            ->where('course_id', $course->id)
            ->where('status', 'completed')
            ->get();

        if ($attempts->count() < 2) {
            throw new Exception("Minimal butuh 2 student yang telah menyelesaikan test untuk menjalankan K-Means.");
        }

        $matrix = [];
        $attemptIds = [];

        foreach ($attempts as $attempt) {
            $scores = $attempt->componentScores;
            $row = [];

            // 1. Goal Setting (Contribution %)
            $row['mastery'] = $scores->where('dimension.name', 'Mastery Goal')->first()?->contribution_pct ?? 0;
            $row['performance'] = $scores->where('dimension.name', 'Performance Goal')->first()?->contribution_pct ?? 0;

            // 2. Prior Knowledge (Percentage)
            $row['knowledge'] = $scores->whereNull('dimension_id')->whereNull('component_id')->first()?->average_score ?? 0;

            // 3. Motivational Profile / SDT (Contribution %)
            $row['autonomy'] = $scores->where('dimension.name', 'Autonomy')->first()?->contribution_pct ?? 0;
            $row['competence'] = $scores->where('dimension.name', 'Competence')->first()?->contribution_pct ?? 0;
            $row['relatedness'] = $scores->where('dimension.name', 'Relatedness')->first()?->contribution_pct ?? 0;

            // 4. AI Interaction Preference (Average 1-5)
            $row['transparency'] = $scores->where('dimension.name', 'Transparency')->first()?->average_score ?? 0;
            $row['guidance'] = $scores->where('dimension.name', 'Guidance')->first()?->average_score ?? 0;
            $row['adaptivity'] = $scores->where('dimension.name', 'Adaptivity')->first()?->average_score ?? 0;
            $row['feedback'] = $scores->where('dimension.name', 'Feedback')->first()?->average_score ?? 0;

            $matrix[] = array_values($row);
            $attemptIds[] = $attempt->id;
        }

        return [
            'matrix' => $matrix,
            'attempt_ids' => $attemptIds
        ];
    }

    /**
     * Find optimal K using Elbow Method (Inertia) and Silhouette Score.
     * Evaluates K from $kMin to $kMax (max cannot exceed sample size).
     */
    public function findOptimalK(array $featureMatrix, int $kMin = 2, int $kMax = 8): array
    {
        $samplesCount = count($featureMatrix);
        $actualMaxK = min($kMax, $samplesCount - 1);

        if ($actualMaxK < $kMin) {
            return [
                'optimal_k' => 2,
                'elbow_data' => [2 => 0]
            ];
        }

        $dataset = new Unlabeled($featureMatrix);
        $dataset->apply(new NumericStringConverter())
                ->apply(new ZScaleStandardizer());

        $elbowData = [];
        $numTrials = 5; // Jalankan 5x per K, ambil rata-rata inertia

        for ($k = $kMin; $k <= $actualMaxK; $k++) {
            $totalInertia = 0;
            for ($trial = 0; $trial < $numTrials; $trial++) {
                srand(42 + $trial); // Seed deterministik per trial
                $estimator = new KMeans($k);
                $estimator->train(clone $dataset);
                $losses = $estimator->losses();
                $totalInertia += (is_array($losses) && count($losses) > 0) ? end($losses) : 0;
            }
            $elbowData[$k] = $totalInertia / $numTrials;
        }

        // Find Elbow using distance to line method (Kneedle algorithm)
        $optimalK = $kMin;
        $maxDistance = -1;

        $x1 = $kMin;
        $y1 = $elbowData[$kMin];
        $x2 = $actualMaxK;
        $y2 = $elbowData[$actualMaxK];

        $denominator = sqrt(pow($y2 - $y1, 2) + pow($x2 - $x1, 2));

        if ($denominator > 0) {
            for ($k = $kMin; $k <= $actualMaxK; $k++) {
                $x0 = $k;
                $y0 = $elbowData[$k];

                $numerator = abs(($y2 - $y1) * $x0 - ($x2 - $x1) * $y0 + $x2 * $y1 - $y2 * $x1);
                $distance = $numerator / $denominator;

                if ($distance > $maxDistance) {
                    $maxDistance = $distance;
                    $optimalK = $k;
                }
            }
        }

        return [
            'optimal_k' => $optimalK,
            'elbow_data' => $elbowData
        ];
    }

    /**
     * Run K-Means with specified K value
     */
    public function run(array $featureMatrix, int $k): array
    {
        $dataset = new Unlabeled($featureMatrix);

        $dataset->apply(new NumericStringConverter())
                ->apply(new ZScaleStandardizer());

        // Seed tetap → hasil SELALU identik pada data yang sama
        srand(42);
        $estimator = new KMeans($k);
        $estimator->train($dataset);

        $predictions = $estimator->predict($dataset);

        return $predictions;
    }

    /**
     * Reduce feature matrix to 2 dimensions for scatter plot visualization using PCA.
     */
    public function buildPcaScatterData(array $featureMatrix, array $predictions, array $attemptIds): array
    {
        $scatterData = [];

        foreach ($featureMatrix as $index => $sample) {
            $cluster = $predictions[$index];
            
            // X axis: Mastery Goal Score (index 0)
            $x = $sample[0] ?? 0;

            // Y axis: Prior Knowledge Score (index 2)
            $y = $sample[2] ?? 0;

            $scatterData[] = [
                'attempt_id' => $attemptIds[$index],
                'x' => round($x, 2),
                'y' => round($y, 2),
                'cluster' => $cluster
            ];
        }

        return $scatterData;
    }

    /**
     * Orchestrate the entire K-Means pipeline for a course
     */
    public function executeClustering(Course $course): KmeansRun
    {
        $data = $this->buildFeatureMatrix($course);
        $matrix = $data['matrix'];
        $attemptIds = $data['attempt_ids'];

        $optimalData = $this->findOptimalK($matrix);
        $optimalK = $optimalData['optimal_k'];

        $predictions = $this->run($matrix, $optimalK);
        $scatterData = $this->buildPcaScatterData($matrix, $predictions, $attemptIds);

        $optimalData['scatter_data'] = $scatterData;

        $run = KmeansRun::create([
            'course_id' => $course->id,
            'triggered_by' => auth()->id(),
            'status' => 'running',
        ]);

        $this->saveResults($run, $optimalData, $attemptIds, $predictions, $scatterData);

        return $run;
    }

    /**
     * Save K-Means results and assignments to DB
     */
    public function saveResults(KmeansRun $run, array $runResults, array $attemptIds, array $predictions, array $scatterData = []): void
    {
        // Update run status and summary
        $run->update([
            'status' => 'completed',
            'k_value' => $runResults['optimal_k'],
            'result_summary' => $runResults, // contains elbow, silhouette, and scatter data
        ]);

        // Delete any existing assignments for this run (just in case)
        KmeansClusterAssignment::where('run_id', $run->id)->delete();

        $assignments = [];
        foreach ($predictions as $index => $clusterNum) {
            $assignments[] = [
                'run_id' => $run->id,
                'attempt_id' => $attemptIds[$index],
                'cluster_number' => $clusterNum + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        KmeansClusterAssignment::insert($assignments);
    }
}

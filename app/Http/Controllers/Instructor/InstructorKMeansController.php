<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\KmeansRun;
use App\Services\KMeansService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstructorKMeansController extends Controller
{
    private KMeansService $kMeansService;

    public function __construct(KMeansService $kMeansService)
    {
        $this->kMeansService = $kMeansService;
    }

    /**
     * Menjalankan analisis K-Means untuk kursus milik instruktur.
     */
    public function run(Course $course)
    {
        if ($course->instructor_id !== Auth::id()) {
            abort(403);
        }

        if ($course->type !== 'adaptive') {
            return redirect()->route('instructor.kmeans.show', $course)
                ->with('error', 'K-Means hanya tersedia untuk kursus Adaptive.');
        }

        $completedAttempts = $course->profilingAttempts()->where('status', 'completed')->count();
        if ($completedAttempts < 3) {
            return redirect()->route('instructor.kmeans.show', $course)
                ->with('error', 'Minimal 3 siswa harus menyelesaikan tes profiling sebelum analisis dapat dijalankan.');
        }

        try {
            DB::beginTransaction();
            $run = $this->kMeansService->executeClustering($course);
            DB::commit();

            return redirect()->route('instructor.kmeans.show', $course)
                ->with('success', 'Analisis K-Means berhasil dijalankan (K=' . $run->k_value . '). Hasil terbaru ditampilkan di bawah.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('instructor.kmeans.show', $course)
                ->with('error', 'Gagal menjalankan K-Means: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan hasil analisis K-Means (read-only untuk instruktur).
     */
    public function show(Course $course)
    {
        // Pastikan kursus milik instruktur yang sedang login
        if ($course->instructor_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke kursus ini.');
        }

        // Pastikan kursus bertipe adaptive
        if ($course->type !== 'adaptive') {
            return redirect()->route('instructor.courses.index')
                ->with('error', 'Analisis K-Means hanya tersedia untuk kursus bertipe Adaptive.');
        }

        // Ambil run K-Means terbaru untuk kursus ini
        $latestRun = KmeansRun::where('course_id', $course->id)
            ->with('clusterAssignments.attempt.student')
            ->latest()
            ->first();

        if (!$latestRun) {
            return view('instructor.kmeans.show', compact('course', 'latestRun'));
        }

        // Susun data Chart.js
        $chartData = [];
        $scatterData = collect($latestRun->result_summary['scatter_data'] ?? []);
        $clusters = $scatterData->groupBy('cluster');

        $colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40',
        ];

        $assignmentsMap = $latestRun->clusterAssignments->keyBy('attempt_id');

        // Hitung tabel detail (raw scores + centroids)
        $tableData = [];
        $clusterCentroids = [];
        $clusterCounts = [];
        $features = ['mastery', 'performance', 'knowledge', 'autonomy', 'competence', 'relatedness', 'transparency', 'guidance', 'adaptivity', 'feedback'];

        $i = 0;
        foreach ($clusters as $clusterIndex => $points) {
            $clusterNum = $clusterIndex + 1;

            $clusterCentroids[$clusterNum] = array_fill_keys($features, 0);
            $clusterCounts[$clusterNum] = 0;

            $dataPoints = [];
            foreach ($points as $point) {
                $assignment = $assignmentsMap->get($point['attempt_id']);
                $studentName = 'Unknown';

                if ($assignment && $assignment->attempt) {
                    $studentName = $assignment->attempt->student->name ?? 'Unknown';
                    $scores = $assignment->attempt->componentScores;

                    $rowScores = [
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

                    $tableData[] = [
                        'student_name' => $studentName,
                        'cluster'      => $clusterNum,
                        'scores'       => $rowScores,
                    ];

                    foreach ($features as $f) {
                        $clusterCentroids[$clusterNum][$f] += $rowScores[$f];
                    }
                    $clusterCounts[$clusterNum]++;
                }

                $dataPoints[] = [
                    'x'            => $point['x'],
                    'y'            => $point['y'],
                    'student_name' => $studentName,
                ];
            }

            // Hitung rata-rata centroid
            if ($clusterCounts[$clusterNum] > 0) {
                foreach ($features as $f) {
                    $clusterCentroids[$clusterNum][$f] = round($clusterCentroids[$clusterNum][$f] / $clusterCounts[$clusterNum], 2);
                }
            }

            $chartData[] = [
                'label'           => 'Kluster ' . $clusterNum,
                'data'            => $dataPoints,
                'backgroundColor' => $colors[$i % count($colors)],
            ];
            $i++;
        }

        return view('instructor.kmeans.show', compact('course', 'latestRun', 'chartData', 'tableData', 'clusterCentroids'));
    }
}

<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\KmeansRun;
use App\Services\KMeansService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KMeansController extends Controller
{
    private KMeansService $kMeansService;

    public function __construct(KMeansService $kMeansService)
    {
        $this->kMeansService = $kMeansService;
    }

    public function index()
    {
        // Hanya ambil kursus bertipe adaptive
        $courses = Course::where('type', 'adaptive')->withCount('profilingAttempts')->get();
        return view('superadmin.kmeans.index', compact('courses'));
    }

    public function run(Request $request, Course $course)
    {
        // Pastikan kursus bertipe adaptive
        if ($course->type !== 'adaptive') {
            return back()->with('error', 'Kursus bukan tipe adaptive.');
        }

        // Minimal ada 5 siswa yang selesai agar k-means masuk akal, bisa disesuaikan
        $completedAttempts = $course->profilingAttempts()->where('status', 'completed')->count();
        if ($completedAttempts < 3) {
            return back()->with('error', 'Belum cukup data siswa (minimal 3 siswa yang selesai tes) untuk clustering.');
        }

        try {
            DB::beginTransaction();
            $run = $this->kMeansService->executeClustering($course);
            DB::commit();

            return redirect()->route('superadmin.kmeans.show', $course->id)->with('success', 'K-Means clustering berhasil dijalankan (K=' . $run->k_value . ').');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menjalankan K-Means: ' . $e->getMessage());
        }
    }

    public function show(Course $course)
    {
        // Ambil run terakhir untuk kursus ini
        $latestRun = KmeansRun::where('course_id', $course->id)
            ->with('clusterAssignments.attempt.student')
            ->latest()
            ->first();

        if (!$latestRun) {
            return redirect()->route('superadmin.kmeans.index')->with('error', 'Belum ada hasil clustering untuk kursus ini.');
        }

        // Susun data untuk Chart.js (Scatter Plot) dari JSON result_summary
        $chartData = [];
        $scatterData = collect($latestRun->result_summary['scatter_data'] ?? []);
        $clusters = $scatterData->groupBy('cluster');
        
        // Buat warna random untuk tiap kluster
        $colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#E7E9ED', '#8A2BE2'
        ];

        $assignmentsMap = $latestRun->clusterAssignments->keyBy('attempt_id');

        $tableData = [];
        $clusterCentroids = [];
        $clusterCounts = [];

        $i = 0;
        foreach ($clusters as $clusterIndex => $points) {
            $clusterNum = $clusterIndex + 1;
            
            // Initialize centroid for this cluster
            $clusterCentroids[$clusterNum] = [
                'mastery' => 0, 'performance' => 0, 'knowledge' => 0,
                'autonomy' => 0, 'competence' => 0, 'relatedness' => 0,
                'transparency' => 0, 'guidance' => 0, 'adaptivity' => 0, 'feedback' => 0
            ];
            $clusterCounts[$clusterNum] = 0;

            $dataPoints = [];
            foreach ($points as $point) {
                $assignment = $assignmentsMap->get($point['attempt_id']);
                $studentName = 'Unknown';
                
                if ($assignment && $assignment->attempt) {
                    $studentName = $assignment->attempt->student->name ?? 'Unknown';
                    $scores = $assignment->attempt->componentScores;
                    
                    $mastery = $scores->where('dimension.name', 'Mastery Goal')->first()?->contribution_pct ?? 0;
                    $performance = $scores->where('dimension.name', 'Performance Goal')->first()?->contribution_pct ?? 0;
                    $knowledge = $scores->whereNull('dimension_id')->whereNull('component_id')->first()?->average_score ?? 0;
                    $autonomy = $scores->where('dimension.name', 'Autonomy')->first()?->contribution_pct ?? 0;
                    $competence = $scores->where('dimension.name', 'Competence')->first()?->contribution_pct ?? 0;
                    $relatedness = $scores->where('dimension.name', 'Relatedness')->first()?->contribution_pct ?? 0;
                    $transparency = $scores->where('dimension.name', 'Transparency')->first()?->average_score ?? 0;
                    $guidance = $scores->where('dimension.name', 'Guidance')->first()?->average_score ?? 0;
                    $adaptivity = $scores->where('dimension.name', 'Adaptivity')->first()?->average_score ?? 0;
                    $feedback = $scores->where('dimension.name', 'Feedback')->first()?->average_score ?? 0;
                    
                    $tableData[] = [
                        'student_name' => $studentName,
                        'cluster' => $clusterNum,
                        'scores' => [
                            'mastery' => $mastery, 'performance' => $performance, 'knowledge' => $knowledge,
                            'autonomy' => $autonomy, 'competence' => $competence, 'relatedness' => $relatedness,
                            'transparency' => $transparency, 'guidance' => $guidance, 'adaptivity' => $adaptivity, 'feedback' => $feedback
                        ],
                        'date' => $assignment->attempt->updated_at ?? null
                    ];

                    $clusterCentroids[$clusterNum]['mastery'] += $mastery;
                    $clusterCentroids[$clusterNum]['performance'] += $performance;
                    $clusterCentroids[$clusterNum]['knowledge'] += $knowledge;
                    $clusterCentroids[$clusterNum]['autonomy'] += $autonomy;
                    $clusterCentroids[$clusterNum]['competence'] += $competence;
                    $clusterCentroids[$clusterNum]['relatedness'] += $relatedness;
                    $clusterCentroids[$clusterNum]['transparency'] += $transparency;
                    $clusterCentroids[$clusterNum]['guidance'] += $guidance;
                    $clusterCentroids[$clusterNum]['adaptivity'] += $adaptivity;
                    $clusterCentroids[$clusterNum]['feedback'] += $feedback;
                    $clusterCounts[$clusterNum]++;
                }

                $dataPoints[] = [
                    'x' => $point['x'],
                    'y' => $point['y'],
                    'student_name' => $studentName
                ];
            }

            if ($clusterCounts[$clusterNum] > 0) {
                foreach ($clusterCentroids[$clusterNum] as $key => $val) {
                    $clusterCentroids[$clusterNum][$key] = round($val / $clusterCounts[$clusterNum], 2);
                }
            }

            $chartData[] = [
                'label' => 'Kluster ' . $clusterNum,
                'data' => $dataPoints,
                'backgroundColor' => $colors[$i % count($colors)],
            ];
            $i++;
        }

        // ==========================================
        // Z-SCORE STANDARDIZATION & EUCLIDEAN DISTANCE
        // ==========================================
        $features = ['mastery', 'performance', 'knowledge', 'autonomy', 'competence', 'relatedness', 'transparency', 'guidance', 'adaptivity', 'feedback'];
        $n = count($tableData);
        $means = [];
        $stdDevs = [];
        
        foreach ($features as $f) {
            $sum = 0;
            foreach ($tableData as $row) {
                $sum += $row['scores'][$f];
            }
            $mean = $n > 0 ? $sum / $n : 0;
            $means[$f] = $mean;
            
            $varianceSum = 0;
            foreach ($tableData as $row) {
                $varianceSum += pow($row['scores'][$f] - $mean, 2);
            }
            $variance = $n > 0 ? $varianceSum / $n : 0; // Rubix uses population variance
            $stdDevs[$f] = sqrt($variance);
        }

        foreach ($tableData as &$row) {
            $row['z_scores'] = [];
            foreach ($features as $f) {
                $sd = $stdDevs[$f] > 0 ? $stdDevs[$f] : 1;
                $row['z_scores'][$f] = ($row['scores'][$f] - $means[$f]) / $sd;
            }
        }
        unset($row);

        $centroidZScores = [];
        foreach ($clusterCentroids as $clusterNum => $centroid) {
            $centroidZScores[$clusterNum] = [];
            foreach ($features as $f) {
                $sd = $stdDevs[$f] > 0 ? $stdDevs[$f] : 1;
                $centroidZScores[$clusterNum][$f] = ($centroid[$f] - $means[$f]) / $sd;
            }
        }

        foreach ($tableData as &$row) {
            $distances = [];
            foreach ($centroidZScores as $clusterNum => $cZScores) {
                $dist = 0;
                foreach ($features as $f) {
                    $dist += pow($row['z_scores'][$f] - $cZScores[$f], 2);
                }
                $distances[$clusterNum] = sqrt($dist);
            }
            $row['distances'] = $distances;
        }
        unset($row);

        return view('superadmin.kmeans.show', compact('course', 'latestRun', 'chartData', 'tableData', 'clusterCentroids'));
    }
}

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

        $i = 0;
        foreach ($clusters as $clusterIndex => $points) {
            $dataPoints = [];
            foreach ($points as $point) {
                $assignment = $assignmentsMap->get($point['attempt_id']);
                $dataPoints[] = [
                    'x' => $point['x'],
                    'y' => $point['y'],
                    'student_name' => $assignment->attempt->student->name ?? 'Unknown'
                ];
            }

            $chartData[] = [
                'label' => 'Kluster ' . ($clusterIndex + 1),
                'data' => $dataPoints,
                'backgroundColor' => $colors[$i % count($colors)],
            ];
            $i++;
        }

        return view('superadmin.kmeans.show', compact('course', 'latestRun', 'chartData'));
    }
}

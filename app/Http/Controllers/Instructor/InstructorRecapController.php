<?php

namespace App\Http\Controllers\Instructor;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Support\Str;
use App\Models\PointHistory;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ModuleRecapExport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class InstructorRecapController extends Controller
{
    public function index(Course $course)
    {
        if ($course->instructor_id != Auth::id()) {
            abort(403);
        }
        $course->load('modules');
        return view('instructor.recap.index', compact('course'));
    }

    public function getModuleRecapData(Module $module)
    {
        if ($module->course->instructor_id != Auth::id()) {
            abort(403);
        }

        list($students, $gradableLessons, $scores) = $this->prepareRecapData($module);

        $html = view('instructor.recap.partials._recap_table', compact('students', 'gradableLessons', 'scores', 'module'))->render();
        return response()->json(['html' => $html]);
    }

    public function downloadPdf(Module $module)
    {
        if ($module->course->instructor_id != Auth::id()) {
            abort(403);
        }
        list($students, $gradableLessons, $scores) = $this->prepareRecapData($module);
        $pdf = Pdf::loadView('instructor.recap.pdf_template', compact('students', 'gradableLessons', 'scores', 'module'))->setPaper('a4', 'landscape');
        return $pdf->download('rekap-nilai-' . Str::slug($module->title) . '.pdf');
    }

    public function downloadExcel(Module $module)
    {
        if ($module->course->instructor_id != Auth::id()) {
            abort(403);
        }
        list($students, $gradableLessons, $scores) = $this->prepareRecapData($module);
        return Excel::download(new ModuleRecapExport($students, $gradableLessons, $scores, $module), 'rekap-nilai-' . Str::slug($module->title) . '.xlsx');
    }

    /**
     * Helper method untuk menyiapkan data rekap.
     */
    private function prepareRecapData(Module $module)
    {
        // Get students and sort them by unique_id_number
        $students = $module->course->students()
            ->with('studentProfile')
            ->get()
            ->sortBy(function ($student) {
                // Put null values at the bottom
                return $student->studentProfile && $student->studentProfile->unique_id_number
                    ? (int)$student->studentProfile->unique_id_number
                    : PHP_INT_MAX; // Using PHP_INT_MAX to push nulls to the bottom
            });

        $gradableLessons = $module->lessons()
            ->whereIn('lessonable_type', [
                \App\Models\Quiz::class,
                \App\Models\LessonAssignment::class,
                \App\Models\LessonPoint::class,
            ])
            ->with('lessonable')
            ->orderBy('order')
            ->get();

        $lessonIdsInModule = $gradableLessons->pluck('id');

        $scores = [];

        foreach ($students as $student) {
            $scores[$student->id] = [];

            $coursePoints = $student->coursePoints()->where('course_id', $module->course_id)->first();
            $scores[$student->id]['total_course_points'] = $coursePoints->pivot->points_earned ?? 0;

            // Hitung total poin di modul ini
            $scores[$student->id]['total_module_points'] = $student->pointHistories()
                ->whereIn('lesson_id', $lessonIdsInModule)
                ->sum('points');
            foreach ($gradableLessons as $lesson) {
                $score = '-'; // Default
                $lessonable = $lesson->lessonable;

                if ($lessonable instanceof \App\Models\Quiz) {
                    $bestAttempt = $student->quizAttempts()
                        ->where('quiz_id', $lessonable->id)
                        ->where('status', 'passed')
                        ->orderBy('score', 'desc')
                        ->first();
                    $quiz = $lessonable;
                    $quizMaxScore = $quiz->questions()->sum('score');
                    $quizMinimumScore = $quizMaxScore * ($quiz->pass_mark / 100);
                    if ($bestAttempt) {
                        // Gunakan effective_score (revised_score → scaled_score → kalkulasi manual)
                        // agar rekap nilai mencerminkan skor yang sudah direvisi instruktur
                        if ($bestAttempt->revised_score !== null) {
                            // Jika ada revisi skor, gunakan revised_score (skor mentah) lalu scale
                            $rawScore = $bestAttempt->revised_score;
                            $scaledScore = ($quizMaxScore > 0) ? min(100, round(($rawScore / $quizMaxScore) * 100, 2)) : 0;
                        } elseif ($bestAttempt->scaled_score !== null) {
                            // Jika ada scaled_score, gunakan langsung
                            $rawScore = $bestAttempt->score;
                            $scaledScore = $bestAttempt->scaled_score;
                        } else {
                            // Fallback untuk data lama: hitung manual
                            $rawScore = $bestAttempt->score;
                            $scaledScore = ($quizMaxScore > 0) ? min(100, round(($rawScore / $quizMaxScore) * 100, 2)) : 0;
                        }
                        // Rumus: (Skor yang diperoleh / Skor maksimum) × 100
                        // Format the score with comma as decimal separator and period for thousands
                        $score = rtrim(rtrim(number_format($scaledScore, 2, ',', '.'), '0'), ',');

                        // Store both raw and scaled scores
                        $scores[$student->id][$lesson->id . '_raw'] = rtrim(rtrim(number_format($rawScore, 2, ',', '.'), '0'), ',');

                        // Tandai jika skor sudah direvisi
                        if ($bestAttempt->revised_score !== null) {
                            $scores[$student->id][$lesson->id . '_revised'] = true;
                        }
                    }
                } elseif ($lessonable instanceof \App\Models\LessonAssignment) {
                    $submission = $student->assignmentSubmissions()
                        ->where('assignment_id', $lessonable->id)
                        ->first();
                    if ($submission && !is_null($submission->grade)) {
                        $score = $submission->grade;
                    }
                } elseif ($lessonable instanceof \App\Models\LessonPoint) {
                    $points = DB::table('lesson_point_awards')
                        ->where('lesson_id', $lesson->id)
                        ->where('student_id', $student->id)
                        ->sum('points');
                    $score = $points > 0 ? $points : '-';
                }
                $scores[$student->id][$lesson->id] = $score;
            }
        }


        // --- LOGIKA BARU: Hitung nilai kelulusan absolut untuk Kuis ---
        foreach ($gradableLessons as $lesson) {
            if ($lesson->lessonable_type === 'App\Models\Quiz') {
                $totalScore = $lesson->lessonable->questions->sum('score');
                $passMarkPercentage = $lesson->lessonable->pass_mark;

                // Store original max score for reference
                $lesson->lessonable->max_possible_score = $totalScore;

                // Pass mark is already a percentage (0-100), so we can use it directly
                $lesson->lessonable->minimum_passing_score = $passMarkPercentage;
            }
        }
        // --- AKHIR LOGIKA BARU ---

        return [$students, $gradableLessons, $scores];
    }
}

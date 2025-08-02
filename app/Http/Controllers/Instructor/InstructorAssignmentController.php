<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Models\LessonAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PointService;

class InstructorAssignmentController extends Controller
{
    /**
     * Menampilkan daftar semua pengumpulan tugas untuk pelajaran tertentu.
     */
    public function index(LessonAssignment $assignment)
    {
        // Otorisasi: Pastikan instruktur yang mengakses adalah pemilik kursus

        // Ambil semua data pengumpulan, beserta data siswa, urutkan dari yang terbaru
        $submissions = $assignment->submissions()
            ->with('user')
            ->latest('submitted_at')
            ->paginate(15);

        return view('instructor.assignments.submissions', compact('assignment', 'submissions'));
    }



    public function grade(Request $request, AssignmentSubmission $submission)
    {
        // if ($submission->assignment->lesson->module->course->instructor_id !== Auth::id()) {
        //     abort(403, 'Akses ditolak.');
        // }

        $validated = $request->validate([
            'grade' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $assignment = $submission->assignment;
        $student = $submission->user;
        $lesson = $assignment->lesson;

        // Cek apakah siswa sudah pernah lulus tugas ini sebelumnya
        $hasPassedBefore = $student->assignmentSubmissions()
            ->where('assignment_id', $assignment->id)
            ->where('status', 'passed')
            ->exists();

        $newStatus = 'revision_required';
        if ($validated['grade'] >= $assignment->pass_mark) {
            $newStatus = 'passed';

            // Tandai pelajaran sebagai selesai
            $student->completedLessons()->syncWithoutDetaching($lesson->id);

            // Berikan poin HANYA jika statusnya 'passed' DAN belum pernah lulus sebelumnya
            if (!$hasPassedBefore) {
                PointService::addPoints($student, $lesson, $lesson->module->course, 'pass_assignment', $lesson->title);
            }
        }

        $submission->update([
            'grade' => $validated['grade'],
            'feedback' => $validated['feedback'],
            'status' => $newStatus,
        ]);

        return back()->with('success', 'Tugas berhasil dinilai.');
    }


    /**
     * Menyimpan nilai dan umpan balik, serta memicu progres otomatis.
     */
    // public function grade(Request $request, AssignmentSubmission $submission)
    // {


    //     $validated = $request->validate([
    //         'grade' => 'required|numeric|min:0|max:100',
    //         'feedback' => 'nullable|string',
    //     ]);

    //     $assignment = $submission->assignment;
    //     $newStatus = 'revision_required'; 


    //     if ($validated['grade'] >= $assignment->pass_mark) {
    //         $newStatus = 'passed';

    //         // --- LOGIKA PROGRES OTOMATIS ---
    //         $student = $submission->user;
    //         $lesson = $assignment->lesson;
    //         $student->completedLessons()->syncWithoutDetaching($lesson->id);
    //     }

    //     // Perbarui record pengumpulan dengan nilai, feedback, dan status baru
    //     $submission->update([
    //         'grade' => $validated['grade'],
    //         'feedback' => $validated['feedback'],
    //         'status' => $newStatus, // Simpan status baru
    //     ]);

    //     return back()->with('success', 'Tugas berhasil dinilai.');
    // }
}

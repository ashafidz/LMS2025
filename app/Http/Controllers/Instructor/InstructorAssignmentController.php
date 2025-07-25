<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Models\LessonAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorAssignmentController extends Controller
{
    /**
     * Menampilkan daftar semua pengumpulan tugas untuk pelajaran tertentu.
     */
    public function index(LessonAssignment $assignment)
    {
        // Otorisasi: Pastikan instruktur yang mengakses adalah pemilik kursus
        if ($assignment->lesson->module->course->instructor_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        // Ambil semua data pengumpulan, beserta data siswa, urutkan dari yang terbaru
        $submissions = $assignment->submissions()
            ->with('user')
            ->latest('submitted_at')
            ->paginate(15);

        return view('instructor.assignments.submissions', compact('assignment', 'submissions'));
    }

    /**
     * Menyimpan nilai dan umpan balik untuk sebuah pengumpulan tugas.
     */
    public function grade(Request $request, AssignmentSubmission $submission)
    {
        // Otorisasi: Pastikan instruktur adalah pemilik kursus dari tugas ini
        if ($submission->assignment->lesson->module->course->instructor_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'grade' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        // Perbarui record pengumpulan dengan nilai dan feedback
        $submission->update([
            'grade' => $validated['grade'],
            'feedback' => $validated['feedback'],
        ]);

        // Di sini Anda bisa menambahkan logika untuk mengirim notifikasi ke siswa
        // bahwa tugasnya telah dinilai.

        return back()->with('success', 'Tugas berhasil dinilai.');
    }
}

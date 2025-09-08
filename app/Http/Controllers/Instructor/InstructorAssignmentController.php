<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Models\LessonAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PointService;
use Illuminate\Support\Facades\Mail;
use App\Mail\AssignmentRevisionRequired;

class InstructorAssignmentController extends Controller
{
    /**
     * Menampilkan daftar semua pengumpulan tugas untuk pelajaran tertentu,
     * dikelompokkan berdasarkan status.
     */
    public function index(LessonAssignment $assignment)
    {
        // Otorisasi
        if ($assignment->lesson->module->course->instructor_id != Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $course = $assignment->lesson->module->course;

        // 1. Ambil semua siswa yang terdaftar di kursus dengan sorting berdasarkan unique_id_number
        $enrolledStudents = $course->students()
            ->with('studentProfile')
            ->join('student_profiles', 'users.id', '=', 'student_profiles.user_id')
            ->orderByRaw('CASE WHEN student_profiles.unique_id_number IS NULL THEN 1 ELSE 0 END')
            ->orderBy('student_profiles.unique_id_number', 'asc')
            ->select('users.*')
            ->get();

        // 2. Ambil semua pengumpulan untuk tugas ini, diindeks berdasarkan user_id
        $submissions = $assignment->submissions()
            ->with(['user', 'user.studentProfile'])
            ->get()
            ->keyBy('user_id');

        // 3. Kelompokkan siswa ke dalam kategori
        $passedSubmissions = collect();
        $revisionSubmissions = collect();
        $submittedSubmissions = collect();
        $notSubmittedStudents = collect();

        foreach ($enrolledStudents as $student) {
            if (isset($submissions[$student->id])) {
                $submission = $submissions[$student->id];
                if ($submission->status === 'passed') {
                    $passedSubmissions->push($submission);
                } elseif ($submission->status === 'revision_required') {
                    $revisionSubmissions->push($submission);
                } else { // status 'submitted'
                    $submittedSubmissions->push($submission);
                }
            } else {
                // Siswa yang terdaftar tapi tidak ada di daftar submission
                $notSubmittedStudents->push($student);
            }
        }

        // Optional: Sort each collection by unique_id_number if needed
        // (This might be redundant since we already sorted enrolledStudents)
        $sortByUniqueId = function ($item) {
            $uniqueId = null;
            if (isset($item->user) && $item->user->studentProfile) {
                // For submissions
                $uniqueId = $item->user->studentProfile->unique_id_number;
            } elseif (isset($item->studentProfile)) {
                // For students
                $uniqueId = $item->studentProfile->unique_id_number;
            }
            // Return a tuple: [is_null, value] for proper sorting
            return [$uniqueId === null ? 1 : 0, $uniqueId];
        };

        $passedSubmissions = $passedSubmissions->sortBy($sortByUniqueId)->values();
        $revisionSubmissions = $revisionSubmissions->sortBy($sortByUniqueId)->values();
        $submittedSubmissions = $submittedSubmissions->sortBy($sortByUniqueId)->values();
        // $notSubmittedStudents already sorted from the query

        return view('instructor.assignments.submissions', compact(
            'assignment',
            'passedSubmissions',
            'revisionSubmissions',
            'submittedSubmissions',
            'notSubmittedStudents'
        ));
    }

    public function grade(Request $request, AssignmentSubmission $submission)
    {
        if ($submission->assignment->lesson->module->course->instructor_id != Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

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
        }

        $submission->update([
            'grade' => $validated['grade'],
            'feedback' => $validated['feedback'],
            'status' => $newStatus,
        ]);

        // 3. LOGIKA BARU: Kirim email jika perlu revisi
        if ($newStatus === 'revision_required') {
            try {
                Mail::to($student->email)->send(new AssignmentRevisionRequired($submission));
            } catch (\Exception $e) {
                // Opsional: Catat error jika email gagal terkirim
                // \Log::error("Gagal mengirim email revisi: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Tugas berhasil dinilai.');
    }
}

<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Mail\AssignmentRevisionRequired;
use App\Models\AssignmentSubmission;
use App\Models\LessonAssignment;
use App\Services\BadgeService;
use App\Services\HashIdService;
use App\Support\ListQuery;
use App\Support\TableSort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
            // Pencarian nama, email, atau NIM/NIP/NIDN (dikelompokkan, maksimal 100 karakter).
            ->where(fn ($q) => ListQuery::applySearch($q, ['users.name', 'users.email', 'student_profiles.unique_id_number']))
            ->get();

        // 2. Ambil semua pengumpulan untuk tugas ini, diindeks berdasarkan user_id
        $submissions = $assignment->submissions()
            ->with(['assignment', 'user', 'user.studentProfile'])
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

        // Urutan tabel: default NIM naik; bisa diubah lewat ?sort=nim|nama|nilai|waktu&dir=asc|desc.
        $sortColumns = [
            'nim' => fn ($item) => ($item->user ?? $item)->studentProfile?->unique_id_number,
            'nama' => fn ($item) => ($item->user ?? $item)->name,
            'nilai' => fn ($item) => $item->grade ?? null,
            'waktu' => fn ($item) => $item->submitted_at ?? null,
        ];
        [$tableSort, $tableDir] = TableSort::current($sortColumns, 'nim');

        $passedSubmissions = TableSort::apply($passedSubmissions, $sortColumns, $tableSort, $tableDir);
        $revisionSubmissions = TableSort::apply($revisionSubmissions, $sortColumns, $tableSort, $tableDir);
        $submittedSubmissions = TableSort::apply($submittedSubmissions, $sortColumns, $tableSort, $tableDir);
        // Belum Mengumpulkan: tidak punya nilai atau waktu, jadi hanya NIM atau nama.
        $notSubmittedStudents = TableSort::apply(
            $notSubmittedStudents,
            $sortColumns,
            in_array($tableSort, ['nim', 'nama'], true) ? $tableSort : 'nim',
            in_array($tableSort, ['nim', 'nama'], true) ? $tableDir : 'asc'
        );

        // Setiap tab punya halaman sendiri; jumlah di tab memakai total, bukan isi halaman.
        $perPage = ListQuery::perPage();
        $submittedSubmissions = ListQuery::paginateCollection($submittedSubmissions, $perPage, 'page_submitted')->appends('tab', 'submitted');
        $revisionSubmissions = ListQuery::paginateCollection($revisionSubmissions, $perPage, 'page_revision')->appends('tab', 'revision');
        $passedSubmissions = ListQuery::paginateCollection($passedSubmissions, $perPage, 'page_passed')->appends('tab', 'passed');
        $notSubmittedStudents = ListQuery::paginateCollection($notSubmittedStudents, $perPage, 'page_missing')->appends('tab', 'not-submitted');

        return view('instructor.assignments.submissions', compact(
            'assignment',
            'passedSubmissions',
            'revisionSubmissions',
            'submittedSubmissions',
            'notSubmittedStudents',
            'tableSort',
            'tableDir'
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
            // Tandai pelajaran sebagai selesai (sudah ada sejak submit, tapi untuk pastinya)
            $student->completedLessons()->syncWithoutDetaching($lesson->id);
        } else {
            // Jika direvisi, cabut tanda selesainya
            $student->completedLessons()->detach($lesson->id);
        }

        $submission->update([
            'grade' => $validated['grade'],
            'feedback' => $validated['feedback'],
            'status' => $newStatus,
        ]);

        BadgeService::queueFor($student);

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

    /**
     * Meminta revisi secara masal untuk beberapa tugas siswa yang dipilih.
     */
    public function bulkRevise(Request $request, LessonAssignment $assignment)
    {
        // Otorisasi: pastikan instruktur adalah pemilik kursus
        if ($assignment->lesson->module->course->instructor_id != Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'submission_ids' => 'required|array|min:1',
            'submission_ids.*' => 'required',
            'feedback' => 'nullable|string|max:1000',
        ]);

        // Decode IDs (mendukung format hash maupun integer)
        $decodedIds = array_filter(array_map(function ($id) {
            return HashIdService::decode($id) ?? (is_numeric($id) ? (int) $id : null);
        }, $validated['submission_ids']));

        if (empty($decodedIds)) {
            return back()->with('error', 'Tidak ada data pengumpulan tugas yang valid.');
        }

        // Ambil data submissions milik assignment ini
        $submissions = $assignment->submissions()
            ->whereIn('id', $decodedIds)
            ->with(['user', 'assignment.lesson.module.course'])
            ->get();

        if ($submissions->isEmpty()) {
            return back()->with('error', 'Tidak ada data pengumpulan tugas yang ditemukan.');
        }

        $feedback = $validated['feedback'] ?? 'Tugas belum sesuai kriteria. Silakan perbaiki dan kumpulkan kembali.';
        $lesson = $assignment->lesson;

        DB::transaction(function () use ($submissions, $lesson, $feedback) {
            foreach ($submissions as $submission) {
                $student = $submission->user;

                // Cabut tanda selesai pelajaran
                if ($student) {
                    $student->completedLessons()->detach($lesson->id);
                }

                $submission->update([
                    'grade' => 0,
                    'feedback' => $feedback,
                    'status' => 'revision_required',
                ]);
            }
        });

        // Kirim email notifikasi revisi ke setiap siswa setelah transaksi commit
        foreach ($submissions as $submission) {
            $student = $submission->user;
            if ($student && $student->email) {
                try {
                    Mail::to($student->email)->send(new AssignmentRevisionRequired($submission));
                } catch (\Exception $e) {
                    Log::error("Gagal mengirim email revisi masal ke {$student->email}: ".$e->getMessage());
                }
            }
        }

        $count = $submissions->count();

        return back()->with('success', "{$count} tugas siswa berhasil diminta untuk direvisi.");
    }
}

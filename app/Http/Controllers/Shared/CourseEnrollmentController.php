<?php

namespace App\Http\Controllers\Shared;

use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CourseEnrollmentController extends Controller
{
    /**
     * Menampilkan halaman daftar kursus untuk manajemen pengguna.
     */
    public function index()
    {
        $courses = Course::with('instructor')->latest()->simplePaginate(15);
        return view('shared-admin.course-enrollments.index', compact('courses'));
    }

    // /**
    //  * Menampilkan daftar pengguna yang terdaftar di kursus tertentu.
    //  */
    // public function show(Course $course)
    // {
    //     $enrolledUsers = $course->students()->simplePaginate(20);
    //     return view('shared-admin.course-enrollments.show', compact('course', 'enrolledUsers'));
    // }

    /**
     * Menampilkan daftar pengguna yang terdaftar di kursus tertentu.
     */
    public function show(Course $course)
    {
        $enrolledUsers = $course->students()
            ->with('studentProfile')
            ->orderByRaw('CASE WHEN student_profiles.unique_id_number IS NULL THEN 1 ELSE 0 END')
            ->orderBy('student_profiles.unique_id_number', 'asc')
            ->join('student_profiles', 'users.id', '=', 'student_profiles.user_id')
            ->select('users.*')
            ->simplePaginate(20);

        return view('shared-admin.course-enrollments.show', compact('course', 'enrolledUsers'));
    }

    /**
     * Menampilkan halaman untuk mencari dan menambahkan pengguna baru ke kursus.
     */
    public function create(Request $request, Course $course)
    {
        // Ambil ID semua pengguna yang SUDAH terdaftar di kursus ini
        $enrolledUserIds = $course->students()->pluck('users.id');

        // Query untuk mencari pengguna dengan role 'student' yang BELUM terdaftar
        $query = User::role('student')->whereNotIn('id', $enrolledUserIds);

        // Terapkan filter pencarian jika ada
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $availableUsers = $query->simplePaginate(15);

        return view('shared-admin.course-enrollments.create', compact('course', 'availableUsers'));
    }

    /**
     * Menyimpan (mendaftarkan) pengguna baru ke dalam kursus.
     */
    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Gunakan syncWithoutDetaching untuk menambahkan tanpa menghapus yang lama
        $course->students()->syncWithoutDetaching($validated['user_ids']);

        return redirect()->route(Auth::user()->getRoleNames()->first() . '.course-enrollments.show', $course)
            ->with('success', count($validated['user_ids']) . ' pengguna berhasil ditambahkan.');
    }

    // /**
    //  * Menghapus pendaftaran pengguna beserta semua data progresnya di dalam kursus.
    //  */
    // public function destroy(Request $request, Course $course)
    // {
    //     $validated = $request->validate([
    //         'user_ids' => 'required|array',
    //         'user_ids.*' => 'exists:users,id',
    //     ]);

    //     $usersToDelete = User::find($validated['user_ids']);
    //     // $lessonIdsInCourse = $course->lessons()->pluck('id');
    //     $lessonIdsInCourse = $course->lessons()->pluck('lessons.id');

    //     DB::transaction(function () use ($course, $usersToDelete, $lessonIdsInCourse) {
    //         foreach ($usersToDelete as $user) {
    //             // 1. Hapus data kuis (quiz_attempts)
    //             $user->quizAttempts()
    //                 ->whereIn('quiz_id', function ($query) use ($lessonIdsInCourse) {
    //                     // DIPERBARUI: Query sekarang mencari di tabel 'lessons' yang benar
    //                     $query->select('lessonable_id')
    //                         ->from('lessons')
    //                         ->where('lessonable_type', \App\Models\Quiz::class)
    //                         ->whereIn('id', $lessonIdsInCourse);
    //                 })->delete();

    //             // 2. Hapus data tugas (assignment_submissions) beserta filenya
    //             $submissions = $user->assignmentSubmissions()
    //                 ->whereIn('assignment_id', function ($query) use ($lessonIdsInCourse) {
    //                     // DIPERBARUI: Query sekarang mencari di tabel 'lessons' yang benar
    //                     $query->select('lessonable_id')
    //                         ->from('lessons')
    //                         ->where('lessonable_type', \App\Models\LessonAssignment::class)
    //                         ->whereIn('id', $lessonIdsInCourse);
    //                 })->get();

    //             foreach ($submissions as $submission) {
    //                 if ($submission->file_path) {
    //                     Storage::disk('public')->delete($submission->file_path);
    //                 }
    //                 $submission->delete();
    //             }

    //             // 3. Hapus data pelajaran selesai (lesson_user)
    //             $user->completedLessons()->detach($lessonIdsInCourse);

    //             // 4. Terakhir, hapus pendaftaran dari kursus
    //             $course->students()->detach($user->id);
    //         }
    //     });

    //     return back()->with('success', count($validated['user_ids']) . ' pengguna beserta semua progresnya di kursus ini berhasil dihapus.');
    // }

    /**
     * Menghapus pendaftaran pengguna beserta semua data progres dan poinnya di dalam kursus.
     */
    public function destroy(Request $request, Course $course)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $usersToDelete = User::find($validated['user_ids']);
        $lessonIdsInCourse = $course->lessons()->pluck('lessons.id');

        DB::transaction(function () use ($course, $usersToDelete, $lessonIdsInCourse) {
            foreach ($usersToDelete as $user) {
                // 1. Hapus data kuis (quiz_attempts)
                $user->quizAttempts()
                    ->whereIn('quiz_id', function ($query) use ($lessonIdsInCourse) {
                        $query->select('lessonable_id')
                            ->from('lessons')
                            ->where('lessonable_type', \App\Models\Quiz::class)
                            ->whereIn('id', $lessonIdsInCourse);
                    })->delete();

                // 2. Hapus data tugas (assignment_submissions) beserta filenya
                $submissions = $user->assignmentSubmissions()
                    ->whereIn('assignment_id', function ($query) use ($lessonIdsInCourse) {
                        $query->select('lessonable_id')
                            ->from('lessons')
                            ->where('lessonable_type', \App\Models\LessonAssignment::class)
                            ->whereIn('id', $lessonIdsInCourse);
                    })->get();
                foreach ($submissions as $submission) {
                    if ($submission->file_path) {
                        Storage::disk('public')->delete($submission->file_path);
                    }
                    $submission->delete();
                }

                // 3. Hapus data pelajaran selesai (lesson_user)
                $user->completedLessons()->detach($lessonIdsInCourse);

                // --- LOGIKA BARU: Hapus data poin ---
                // 4. Hapus riwayat perolehan poin di kursus ini
                $user->pointHistories()->where('course_id', $course->id)->delete();

                // 5. Hapus catatan total poin di tabel pivot course_user
                $user->coursePoints()->detach($course->id);
                // --- AKHIR LOGIKA BARU ---

                // 6. Terakhir, hapus pendaftaran dari kursus
                $course->students()->detach($user->id);
            }
        });

        return back()->with('success', count($validated['user_ids']) . ' pengguna beserta semua progres dan poinnya di kursus ini berhasil dihapus.');
    }
}

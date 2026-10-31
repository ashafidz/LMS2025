<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StudentCertificateController extends Controller
{
    /**
     * Menampilkan halaman daftar sertifikat milik pengguna.
     */
    public function index()
    {
        $user = Auth::user();
        $certificates = $user->certificates()
            ->with(['course.modules.lessons'])
            ->latest('issued_at')
            ->simplePaginate(10);

        // Kumpulkan semua ID pelajaran dari kursus yang tampil di halaman ini
        $allLessonIds = $certificates->flatMap(function ($certificate) {
            return $certificate->course ? $certificate->course->modules->flatMap->lessons->pluck('id') : collect();
        })->unique();

        // Ambil ID pelajaran yang sudah diselesaikan siswa dalam 1 query
        $completedLessonIds = $user->completedLessons()
            ->whereIn('lesson_id', $allLessonIds)
            ->pluck('lessons.id')
            ->flip();

        foreach ($certificates as $certificate) {
            if (! $certificate->course) {
                $certificate->has_incomplete_lessons = false;

                continue;
            }

            $courseLessonIds = $certificate->course->modules->flatMap->lessons->pluck('id');
            $totalLessons = $courseLessonIds->count();
            $completedCount = $courseLessonIds->filter(fn ($id) => isset($completedLessonIds[$id]))->count();

            // Jika kursus punya materi dan ada materi baru yang belum diselesaikan
            $certificate->has_incomplete_lessons = ($totalLessons > 0 && $completedCount < $totalLessons);
            $certificate->completed_lessons_count = $completedCount;
            $certificate->total_lessons_count = $totalLessons;
        }

        return view('student.certificates.index', compact('certificates'));
    }
}

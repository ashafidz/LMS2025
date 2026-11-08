<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Support\ListQuery;
use Illuminate\Support\Facades\Auth;

class StudentCertificateController extends Controller
{
    /**
     * Menampilkan halaman daftar sertifikat milik pengguna.
     */
    public function index()
    {
        $user = Auth::user();
        $certificates = ListQuery::applySearch($user->certificates()->with(['course.modules.lessons']), ['course:title'])
            ->latest('issued_at')
            ->paginate(ListQuery::perPage(10, [10, 25, 50]))
            ->withQueryString();

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

        // Shown in the page banner ("kamu sudah meraih N sertifikat"): all certificates, not only the search result.
        $certificateCount = $user->certificates()->count();

        return view('student.certificates.index', compact('certificates', 'certificateCount'));
    }
}

<?php

namespace App\Http\Controllers\Student;

use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    /**
     * Menampilkan pratinjau sertifikat (via AJAX).
     */
    public function preview(Course $course)
    {
        $user = Auth::user();

        if (!$this->isEligibleForCertificate($user, $course)) {
            return response()->json(['success' => false, 'message' => 'Anda belum memenuhi syarat untuk mendapatkan sertifikat.'], 403);
        }

        // Render partial view dan kirim sebagai respons
        $html = view('student.courses.partials._certificate_preview', compact('course', 'user'))->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    /**
     * Membuat dan mengunduh sertifikat dalam format PDF.
     */
    public function download(Course $course)
    {
        $user = Auth::user();

        if (!$this->isEligibleForCertificate($user, $course)) {
            return redirect()->back()->with('error', 'Anda belum memenuhi syarat untuk mengunduh sertifikat.');
        }

        // Buat record sertifikat jika belum ada
        $certificate = $user->certificates()->firstOrCreate(
            ['course_id' => $course->id],
            [
                'certificate_code' => 'LMS-' . $course->id . '-' . $user->id . '-' . time(),
                'issued_at' => now(),
            ]
        );

        // Muat data yang diperlukan untuk PDF
        $data = [
            'user' => $user,
            'course' => $course,
            'certificate' => $certificate,
        ];

        // Render view PDF dan buat file PDF
        $pdf = Pdf::loadView('student.certificates.pdf_template', $data)->setPaper('a4', 'landscape');

        $fileName = 'sertifikat-' . Str::slug($course->title) . '.pdf';

        // Kembalikan PDF sebagai respons unduhan di browser
        return $pdf->download($fileName);
    }

    /**
     * Helper method untuk memeriksa apakah siswa berhak mendapatkan sertifikat.
     */
    private function isEligibleForCertificate(User $user, Course $course): bool
    {
        // Syarat 1: Progres belajar harus 100%
        $totalLessons = $course->lessons()->count();
        $completedLessons = $user->completedLessons()->whereIn('lesson_id', $course->lessons->pluck('id'))->count();

        if ($totalLessons === 0 || $completedLessons < $totalLessons) {
            return false;
        }

        // Syarat 2: Harus sudah memberikan ulasan untuk kursus
        $hasReviewedCourse = $course->reviews()->where('user_id', $user->id)->exists();
        if (!$hasReviewedCourse) {
            return false;
        }

        return true;
    }
}

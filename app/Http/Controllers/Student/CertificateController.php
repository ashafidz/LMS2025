<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    /**
     * Menampilkan pratinjau sertifikat (via AJAX).
     */
    // public function preview(Course $course)
    // {
    //     $user = Auth::user();

    //     if (!$this->isEligibleForCertificate($user, $course)) {
    //         return response()->json(['success' => false, 'message' => 'Anda belum memenuhi syarat untuk mendapatkan sertifikat.'], 403);
    //     }

    //     // Render partial view dan kirim sebagai respons
    //     $html = view('student.courses.partials._certificate_preview', compact('course', 'user'))->render();

    //     return response()->json(['success' => true, 'html' => $html]);
    // }

    /**
     * Menampilkan pratinjau sertifikat (via AJAX).
     */
    public function preview(Course $course)
    {
        $user = Auth::user();

        // Eager load relasi untuk mencegah N+1 query
        $course->load('instructor', 'modules.lessons');

        if (! $this->isEligibleForCertificate($user, $course)) {
            return response()->json(['success' => false, 'message' => 'Anda belum memenuhi syarat untuk mendapatkan sertifikat.'], 403);
        }

        // DIPERBARUI: Ambil atau buat data sertifikat untuk ditampilkan di pratinjau
        $certificate = $user->certificates()->firstOrCreate(
            ['course_id' => $course->id],
            [
                'certificate_code' => 'LMS-'.$course->id.'-'.$user->id.'-'.time(),
                'issued_at' => now(),
            ]
        );

        // Render partial view dan kirim sebagai respons
        $html = view('student.courses.partials._certificate_preview', compact('course', 'user', 'certificate'))->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    /**
     * Membuat dan mengunduh sertifikat dalam format PDF.
     */
    // public function download(Course $course)
    // {
    //     $user = Auth::user();

    //     if (!$this->isEligibleForCertificate($user, $course)) {
    //         return redirect()->back()->with('error', 'Anda belum memenuhi syarat untuk mengunduh sertifikat.');
    //     }

    //     // Buat record sertifikat jika belum ada
    //     $certificate = $user->certificates()->firstOrCreate(
    //         ['course_id' => $course->id],
    //         [
    //             'certificate_code' => 'LMS-' . $course->id . '-' . $user->id . '-' . time(),
    //             'issued_at' => now(),
    //         ]
    //     );

    //     // Muat data yang diperlukan untuk PDF
    //     $data = [
    //         'user' => $user,
    //         'course' => $course,
    //         'certificate' => $certificate,
    //     ];

    //     // Render view PDF dan buat file PDF
    //     $pdf = Pdf::loadView('student.certificates.pdf_template', $data)->setPaper('a4', 'landscape');

    //     $fileName = 'sertifikat-' . Str::slug($course->title) . '.pdf';

    //     // Kembalikan PDF sebagai respons unduhan di browser
    //     return $pdf->download($fileName);
    // }

    public function download(Course $course)
    {
        $user = Auth::user();

        // Eager load relasi untuk mencegah N+1 query
        $course->load('instructor', 'modules.lessons');

        if (! $this->isEligibleForCertificate($user, $course)) {
            return redirect()->back()->with('error', 'Anda belum memenuhi syarat untuk mengunduh sertifikat.');
        }

        $certificate = $user->certificates()->firstOrCreate(
            ['course_id' => $course->id],
            [
                'certificate_code' => 'LMS-'.$course->id.'-'.$user->id.'-'.time(),
                'issued_at' => now(),
            ]
        );

        // Pre-fetch settings untuk menghindari query di view
        $settings = \App\Models\SiteSetting::first();

        // Set timeout timeout yang lebih besar untuk PDF generation
        set_time_limit(120);

        $data = [
            'user' => $user,
            'course' => $course,
            'certificate' => $certificate,
            'settings' => $settings,
        ];

        $fileName = 'sertifikat-'.Str::slug($course->title).'.pdf';

        // PDF disimpan agar tidak di-render ulang (dompdf berat). Nama file berubah jika isi yang dicetak berubah.
        $contentHash = md5(implode('|', [
            $user->name,
            $course->title,
            $course->instructor->name ?? '',
            optional($settings?->updated_at)->timestamp,
        ]));
        $cachedPath = 'certificates/'.$certificate->certificate_code.'-'.$contentHash.'.pdf';

        try {
            if (! Storage::disk('local')->exists($cachedPath)) {
                $pdf = Pdf::loadView('student.certificates.pdf_template', $data)
                    ->setPaper('a4', 'landscape');

                Storage::disk('local')->put($cachedPath, $pdf->output());
            }

            return Storage::disk('local')->download($cachedPath, $fileName);
        } catch (\Exception $e) {
            \Log::error('Certificate PDF generation failed: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal membuat sertifikat. Silakan coba lagi nanti.');
        }
    }

    /**
     * Helper method untuk memeriksa apakah siswa berhak mendapatkan sertifikat.
     */
    private function isEligibleForCertificate(User $user, Course $course): bool
    {
        // Syarat 1: Progres belajar harus 100%
        // Gunakan relasi yang sudah di-load untuk mencegah query tambahan
        $lessons = $course->modules()->with('lessons')->get()->flatMap(function ($module) {
            return $module->lessons;
        });

        $totalLessons = $lessons->count();
        if ($totalLessons === 0) {
            return false;
        }

        $completedLessons = $user->completedLessons()
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->count();

        if ($completedLessons < $totalLessons) {
            return false;
        }

        // Syarat 2: Harus sudah memberikan ulasan untuk kursus
        $hasReviewedCourse = $course->reviews()->where('user_id', $user->id)->exists();
        if (! $hasReviewedCourse) {
            return false;
        }

        return true;
    }
}

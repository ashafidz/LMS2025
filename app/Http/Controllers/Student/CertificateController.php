<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Services\BadgeService;
use App\Services\CertificateRenderer;
use App\Services\CertificateTemplateService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
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

        BadgeService::queueFor($user);

        // Render partial view dan kirim sebagai respons
        $template = app(CertificateTemplateService::class)->active();

        $html = $template
            ? view('certificates._student_preview', [
                'canvas' => app(CertificateRenderer::class)->previewHtml($certificate, $template),
                'course' => $course,
                'user' => $user,
            ])->render()
            : view('student.courses.partials._certificate_preview', compact('course', 'user', 'certificate'))->render();

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
            $totalLessons = $course->modules->flatMap->lessons->count();
            $completedLessons = $user->completedLessons()
                ->whereIn('lesson_id', $course->modules->flatMap->lessons->pluck('id'))
                ->count();

            if ($totalLessons > 0 && $completedLessons < $totalLessons) {
                return redirect()->back()->with('error', 'Kursus telah diperbarui dengan materi baru. Silakan selesaikan seluruh pelajaran untuk mengunduh sertifikat kembali.');
            }

            return redirect()->back()->with('error', 'Anda belum memenuhi syarat untuk mengunduh sertifikat.');
        }

        $certificate = $user->certificates()->firstOrCreate(
            ['course_id' => $course->id],
            [
                'certificate_code' => 'LMS-'.$course->id.'-'.$user->id.'-'.time(),
                'issued_at' => now(),
            ]
        );

        BadgeService::queueFor($user);

        // Pre-fetch settings untuk menghindari query di view
        set_time_limit(120);

        $renderer = app(CertificateRenderer::class);
        $template = app(CertificateTemplateService::class)->active();

        $fileName = 'sertifikat-'.Str::slug($course->title).'.pdf';

        // Kunci cache memuat versi template aktif, jadi mengaktifkan versi lain otomatis membuat PDF baru
        $cachedPath = 'certificates/'.$certificate->certificate_code.'-'.$renderer->cacheKey($certificate, $template).'.pdf';

        try {
            if (! Storage::disk('local')->exists($cachedPath)) {
                Storage::disk('local')->put($cachedPath, $renderer->pdf($certificate, $template)->output());

                // Hapus PDF lama sertifikat ini (versi template sebelumnya) agar tidak menumpuk di disk.
                // glob dengan awalan kode hanya menyentuh file sertifikat ini; tidak membaca seluruh isi folder.
                $current = Storage::disk('local')->path($cachedPath);
                foreach (File::glob(Storage::disk('local')->path('certificates/'.$certificate->certificate_code.'-*.pdf')) as $file) {
                    if ($file !== $current) {
                        File::delete($file);
                    }
                }
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

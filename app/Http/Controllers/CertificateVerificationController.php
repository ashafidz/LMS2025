<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman publik untuk mengecek keaslian sertifikat dari kodenya. Tidak perlu login;
 * yang ditampilkan hanya data yang memang tercetak di sertifikat (tanpa email atau data pribadi lain).
 */
class CertificateVerificationController extends Controller
{
    private const MAX_CODE_LENGTH = 100;

    public function show(Request $request): View
    {
        $code = trim((string) $request->query('kode', ''));
        $certificate = null;

        if ($code !== '' && mb_strlen($code) <= self::MAX_CODE_LENGTH) {
            $certificate = Certificate::where('certificate_code', $code)
                // Kursus yang sudah dihapus tetap ditampilkan: sertifikatnya tetap sah
                ->with(['user', 'course' => fn ($query) => $query->withTrashed()->with('instructor')])
                ->first();
        }

        return view('certificates.verify', [
            'code' => $code,
            'searched' => $code !== '',
            'certificate' => $certificate,
            // Tanggal sama dengan yang tercetak di sertifikat: zona waktu siswa, format Indonesia
            'issuedDate' => $certificate?->issued_at?->copy()
                ->setTimezone($certificate->user->timezone ?: config('app.timezone'))
                ->locale('id')->translatedFormat('j F Y'),
        ]);
    }
}

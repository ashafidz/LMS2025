<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\SiteSetting;
use App\Support\CertificateFonts;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Carbon\Carbon;
use FontLib\Font as FontLibFont;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Membuat sertifikat dari template (PDF dan HTML pratinjau). Tanpa template aktif, desain bawaan lama dipakai.
 * Semua posisi disimpan dalam persen halaman, jadi editor, pratinjau, dan PDF memakai satu sumber kebenaran.
 */
class CertificateRenderer
{
    private const PT_PER_MM = 2.834645669;

    /** Ukuran terkecil teks yang menyusut otomatis, sebagai bagian dari ukuran awal */
    private const MIN_SHRINK_RATIO = 0.6;

    /** @var array<string, array<int, float>> cache lebar huruf per file font (kode huruf => lebar per em) */
    private static array $widthCache = [];

    /** @return array{0: int, 1: int} [lebar mm, tinggi mm] */
    public function pageSizeMm(string $orientation): array
    {
        return $orientation === 'portrait' ? [210, 297] : [297, 210];
    }

    public function pdf(Certificate $certificate, ?CertificateTemplate $template = null): DomPdf
    {
        $template ??= app(CertificateTemplateService::class)->active();
        $certificate->loadMissing(['user', 'course.instructor']);

        if ($template === null) {
            return Pdf::loadView('student.certificates.pdf_template', [
                'user' => $certificate->user,
                'course' => $certificate->course,
                'certificate' => $certificate,
                'settings' => SiteSetting::first(),
            ])->setPaper('a4', 'landscape');
        }

        return Pdf::loadHTML($this->html($certificate, $template))->setPaper('a4', $template->orientation);
    }

    /** PDF contoh dengan data palsu, untuk tombol "Pratinjau PDF" di editor. */
    public function samplePdf(CertificateTemplate $template): DomPdf
    {
        return Pdf::loadHTML($this->pdfHtml($template, $this->sampleValues()))->setPaper('a4', $template->orientation);
    }

    public function html(Certificate $certificate, CertificateTemplate $template): string
    {
        return $this->pdfHtml($template, $this->valuesFor($certificate));
    }

    /** HTML pratinjau (posisi dalam persen) untuk modal siswa dan kartu daftar versi. */
    public function previewHtml(Certificate $certificate, CertificateTemplate $template): string
    {
        return $this->canvasHtml($template, $this->valuesFor($certificate));
    }

    public function sampleCanvasHtml(CertificateTemplate $template): string
    {
        return $this->canvasHtml($template, $this->sampleValues());
    }

    /**
     * Kunci cache file PDF di disk. Berubah jika data sertifikat berubah atau versi template lain diaktifkan.
     */
    public function cacheKey(Certificate $certificate, ?CertificateTemplate $template): string
    {
        $certificate->loadMissing(['user', 'course.instructor']);

        $marker = $template
            ? "t{$template->id}v{$template->version}"
            : 'legacy';

        $parts = [
            $certificate->user->name,
            $certificate->course->title,
            $certificate->course->instructor->name ?? '',
            $this->timezoneFor($certificate),
            $certificate->issued_at->timestamp,
            $template ? '' : optional(SiteSetting::first()?->updated_at)->timestamp,
        ];

        return $marker.'-'.md5(implode('|', $parts));
    }

    public function fitFontSize(string $text, string $fontKey, bool $bold, int $size, float $widthPt): int
    {
        $min = (int) floor($size * self::MIN_SHRINK_RATIO);

        for ($current = $size; $current >= $min; $current--) {
            if ($this->textWidthPt($text, $fontKey, $bold, $current) <= $widthPt) {
                return $current;
            }
        }

        return $min;
    }

    /**
     * Elemen siap tampil: teks sudah diisi, ukuran huruf sudah disesuaikan.
     *
     * @return array<int, array<string, mixed>>
     */
    public function resolveElements(CertificateTemplate $template, array $values): array
    {
        [$pageWidthMm] = $this->pageSizeMm($template->orientation);
        $pageWidthPt = $pageWidthMm * self::PT_PER_MM;
        $resolved = [];

        foreach ($template->elements as $element) {
            $fontKey = in_array($element['font'] ?? '', CertificateFonts::keys(), true) ? $element['font'] : CertificateFonts::DEFAULT;
            $bold = ! empty($element['bold']) && CertificateFonts::supportsBold($fontKey);
            $width = (float) ($element['width'] ?? 50);
            $text = $this->textFor($element, $values);
            $size = (int) ($element['size'] ?? 24);

            if (! empty($element['shrink_to_fit'])) {
                $size = $this->fitFontSize($text, $fontKey, $bold, $size, $width * $pageWidthPt / 100);
            }

            $resolved[] = [
                'key' => $element['key'],
                'text' => $text,
                'x' => (float) ($element['x'] ?? 50),
                'y' => (float) ($element['y'] ?? 50),
                'width' => $width,
                'align' => in_array($element['align'] ?? '', ['left', 'center', 'right'], true) ? $element['align'] : 'left',
                'family' => CertificateFonts::cssFamily($fontKey),
                'size' => $size,
                'color' => preg_match('/^#[0-9A-Fa-f]{6}$/', (string) ($element['color'] ?? '')) ? $element['color'] : '#000000',
                'bold' => $bold,
                'italic' => ! empty($element['italic']) && CertificateFonts::supportsItalic($fontKey),
            ];
        }

        return $resolved;
    }

    private function pdfHtml(CertificateTemplate $template, array $values): string
    {
        // dompdf mendaftarkan font TTF ke storage/fonts saat pertama dipakai. Folder ini tidak ikut deploy
        // (/storage/ dikecualikan), jadi dibuat di sini agar tidak error di server baru.
        File::ensureDirectoryExists(storage_path('fonts'));

        [$pageW, $pageH] = $this->pageSizeMm($template->orientation);

        $elements = array_map(function (array $e) use ($pageW, $pageH) {
            $widthMm = $e['width'] * $pageW / 100;
            $leftMm = $e['x'] * $pageW / 100 - match ($e['align']) {
                'right' => $widthMm,
                'center' => $widthMm / 2,
                default => 0,
            };
            $e['left_mm'] = number_format($leftMm, 2, '.', '');
            $e['top_mm'] = number_format($e['y'] * $pageH / 100, 2, '.', '');
            $e['width_mm'] = number_format($widthMm, 2, '.', '');

            return $e;
        }, $this->resolveElements($template, $values));

        return view('certificates.template-pdf', [
            'pageW' => $pageW,
            'pageH' => $pageH,
            'backgroundPath' => $this->backgroundFile($template),
            'elements' => $elements,
            'fontFaceCss' => CertificateFonts::fontFaceCss(forPdf: true),
            'title' => 'Sertifikat',
        ])->render();
    }

    private function canvasHtml(CertificateTemplate $template, array $values): string
    {
        [$pageW, $pageH] = $this->pageSizeMm($template->orientation);
        $pageWidthPt = $pageW * self::PT_PER_MM;

        $elements = array_map(function (array $e) use ($pageWidthPt) {
            $e['left_pct'] = number_format($e['x'], 2, '.', '');
            $e['top_pct'] = number_format($e['y'], 2, '.', '');
            $e['width_pct'] = number_format($e['width'], 2, '.', '');
            $e['font_cqw'] = number_format($e['size'] / $pageWidthPt * 100, 3, '.', '');
            $e['font_fraction'] = number_format($e['size'] / $pageWidthPt, 5, '.', '');
            $e['translate'] = match ($e['align']) {
                'right' => '-100%',
                'center' => '-50%',
                default => '0',
            };

            return $e;
        }, $this->resolveElements($template, $values));

        return view('certificates._canvas', [
            'pageW' => $pageW,
            'pageH' => $pageH,
            'backgroundUrl' => $template->backgroundUrl(),
            'elements' => $elements,
            'fontFaceCss' => CertificateFonts::fontFaceCss(forPdf: false),
        ])->render();
    }

    /** Path absolut gambar latar: disk publik, atau disk privat untuk gambar sementara pratinjau draf. */
    private function backgroundFile(CertificateTemplate $template): string
    {
        $public = Storage::disk('public');

        return $public->exists($template->background_path)
            ? $public->path($template->background_path)
            : Storage::disk('local')->path($template->background_path);
    }

    private function valuesFor(Certificate $certificate): array
    {
        $certificate->loadMissing(['user', 'course.instructor']);

        return [
            'student_name' => $certificate->user->name,
            'course_title' => $certificate->course->title,
            'instructor_name' => $certificate->course->instructor->name ?? '',
            'certificate_code' => $certificate->certificate_code,
            'issued_at' => $certificate->issued_at->copy()->setTimezone($this->timezoneFor($certificate)),
        ];
    }

    private function sampleValues(): array
    {
        return [
            'student_name' => 'Nama Siswa Contoh Yang Cukup Panjang',
            'course_title' => 'Judul Kursus Contoh',
            'instructor_name' => 'Nama Instruktur',
            'certificate_code' => 'LMS-0-0-0000000000',
            'issued_at' => Carbon::now(),
        ];
    }

    private function timezoneFor(Certificate $certificate): string
    {
        return $certificate->user->timezone ?: config('app.timezone');
    }

    private function textFor(array $element, array $values): string
    {
        return match ($element['key']) {
            'custom_text' => (string) ($element['text'] ?? ''),
            'issued_date' => $this->formatDate($values['issued_at'], $element['date_format'] ?? 'long'),
            default => (string) ($values[$element['key']] ?? ''),
        };
    }

    private function formatDate(Carbon $date, string $format): string
    {
        return $format === 'short'
            ? $date->format('d/m/Y')
            : $date->copy()->locale('id')->translatedFormat('j F Y');
    }

    private function textWidthPt(string $text, string $fontKey, bool $bold, int $size): float
    {
        $widths = $this->glyphWidths($fontKey, $bold);
        $fallback = $widths[110] ?? 0.55; // lebar huruf "n" untuk karakter yang tidak ada di tabel
        $total = 0.0;

        foreach (mb_str_split($text) as $char) {
            $total += $widths[mb_ord($char)] ?? $fallback;
        }

        return $total * $size;
    }

    /** @return array<int, float> kode huruf Unicode => lebar dalam satuan em */
    private function glyphWidths(string $fontKey, bool $bold): array
    {
        $afm = CertificateFonts::afmFile($fontKey, $bold);
        $file = $afm ?? (CertificateFonts::files($fontKey)[0] ?? null);

        if ($file === null || ! is_file($file)) {
            return [];
        }

        return self::$widthCache[$file] ??= $afm ? $this->readAfm($file) : $this->readTtf($file);
    }

    private function readAfm(string $file): array
    {
        $widths = [];
        foreach (file($file, FILE_IGNORE_NEW_LINES) as $line) {
            if (preg_match('/^C\s+(\d+)\s*;\s*WX\s+(\d+)/', $line, $m) && (int) $m[1] > 0) {
                $widths[(int) $m[1]] = ((int) $m[2]) / 1000;
            }
        }

        return $widths;
    }

    private function readTtf(string $file): array
    {
        $font = FontLibFont::load($file);
        $font->parse();

        $unitsPerEm = (int) $font->getData('head', 'unitsPerEm') ?: 1000;
        $hmtx = $font->getData('hmtx');
        $widths = [];

        foreach ($font->getUnicodeCharMap() as $codePoint => $glyphId) {
            if (isset($hmtx[$glyphId])) {
                $widths[$codePoint] = $hmtx[$glyphId][0] / $unitsPerEm;
            }
        }

        return $widths;
    }
}

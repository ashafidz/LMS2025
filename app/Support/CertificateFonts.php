<?php

namespace App\Support;

/**
 * Daftar font yang boleh dipakai di template sertifikat.
 * Helvetica dan Times bawaan dompdf (punya varian tebal). Tiga lainnya dikirim bersama aplikasi di
 * public/fonts/certificates (lisensi SIL OFL, lihat OFL.txt). Montserrat: dua instance statis (Regular 400 dan Bold 700)
 * dari file variable resmi, karena dompdf tidak menerapkan sumbu variable dan memakai bobot bawaan (Thin).
 * Playfair Display (punya Reserved Font Name) dan Great Vibes dipakai apa adanya, hanya Regular.
 */
class CertificateFonts
{
    public const DEFAULT = 'helvetica';

    /** key => [label, css family, regular file, bold file] (file null = font bawaan dompdf) */
    private const FONTS = [
        'helvetica' => ['label' => 'Helvetica', 'family' => 'Helvetica', 'regular' => null, 'bold' => null, 'builtin_bold' => true],
        'times' => ['label' => 'Times', 'family' => 'Times', 'regular' => null, 'bold' => null, 'builtin_bold' => true],
        'montserrat' => ['label' => 'Montserrat', 'family' => 'CertMontserrat', 'regular' => 'montserrat.ttf', 'bold' => 'montserrat-bold.ttf', 'builtin_bold' => false],
        'playfair' => ['label' => 'Playfair Display', 'family' => 'CertPlayfair', 'regular' => 'playfair.ttf', 'bold' => null, 'builtin_bold' => false],
        'greatvibes' => ['label' => 'Great Vibes (huruf sambung)', 'family' => 'CertGreatVibes', 'regular' => 'greatvibes.ttf', 'bold' => null, 'builtin_bold' => false],
    ];

    /** @return array<int, string> */
    public static function keys(): array
    {
        return array_keys(self::FONTS);
    }

    /** @return array<string, string> key => label (untuk dropdown) */
    public static function options(): array
    {
        return array_map(fn ($f) => $f['label'], self::FONTS);
    }

    public static function label(string $key): string
    {
        return (self::FONTS[$key] ?? self::FONTS[self::DEFAULT])['label'];
    }

    public static function cssFamily(string $key): string
    {
        return (self::FONTS[$key] ?? self::FONTS[self::DEFAULT])['family'];
    }

    public static function supportsBold(string $key): bool
    {
        $font = self::FONTS[$key] ?? self::FONTS[self::DEFAULT];

        return $font['builtin_bold'] || $font['bold'] !== null;
    }

    /**
     * Hanya font bawaan dompdf yang punya varian miring. Untuk font TTF yang tidak punya wajah miring, dompdf
     * diam-diam berpindah ke Helvetica jika diminta italic, jadi italic harus dimatikan untuk font itu.
     */
    public static function supportsItalic(string $key): bool
    {
        return (self::FONTS[$key] ?? self::FONTS[self::DEFAULT])['builtin_bold'];
    }

    /** @return array<int, string> path absolut file font yang harus ada untuk $key */
    public static function files(string $key): array
    {
        $font = self::FONTS[$key] ?? self::FONTS[self::DEFAULT];

        return array_map(
            fn ($file) => public_path('fonts/certificates/'.$file),
            array_values(array_filter([$font['regular'], $font['bold']]))
        );
    }

    /** File metrik AFM dompdf untuk font bawaan (null untuk font TTF). Dipakai untuk mengukur lebar teks. */
    public static function afmFile(string $key, bool $bold): ?string
    {
        $name = match ($key) {
            'helvetica' => $bold ? 'Helvetica-Bold' : 'Helvetica',
            'times' => $bold ? 'Times-Bold' : 'Times-Roman',
            default => null,
        };

        return $name ? base_path('vendor/dompdf/dompdf/lib/fonts/'.$name.'.afm') : null;
    }

    /** Daftar font yang punya varian tebal, untuk editor (menonaktifkan checkbox Tebal). */
    public static function boldKeys(): array
    {
        return array_values(array_filter(self::keys(), fn ($k) => self::supportsBold($k)));
    }

    /** Daftar font yang punya varian miring, untuk editor (menonaktifkan checkbox Miring). */
    public static function italicKeys(): array
    {
        return array_values(array_filter(self::keys(), fn ($k) => self::supportsItalic($k)));
    }

    /**
     * Aturan @font-face untuk font yang dikirim bersama aplikasi.
     * PDF memakai path file absolut (di dalam base path, jadi lolos chroot dompdf), browser memakai URL.
     */
    public static function fontFaceCss(bool $forPdf): string
    {
        $css = '';
        foreach (self::FONTS as $font) {
            foreach (['regular' => 'normal', 'bold' => 'bold'] as $variant => $weight) {
                if ($font[$variant] === null) {
                    continue;
                }
                $src = $forPdf
                    ? public_path('fonts/certificates/'.$font[$variant])
                    : asset('fonts/certificates/'.$font[$variant]);
                $css .= "@font-face { font-family: '{$font['family']}'; font-weight: {$weight}; font-style: normal; src: url('{$src}') format('truetype'); }\n";
            }
        }

        return $css;
    }
}

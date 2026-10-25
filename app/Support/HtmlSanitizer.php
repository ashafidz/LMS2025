<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\Cache;

/**
 * Membersihkan HTML buatan instruktur (editor artikel, deskripsi kursus) dari script dan atribut berbahaya.
 * Dipakai saat menyimpan DAN saat menampilkan, supaya data lama juga aman tanpa migration.
 */
class HtmlSanitizer
{
    // Di atas ukuran ini (misalnya artikel berisi gambar base64 besar) hasil tidak disimpan di cache
    private const MAX_CACHEABLE_BYTES = 200000;

    // Naikkan versinya setiap kali allowlist di purifier() diubah, supaya hasil lama di cache tidak dipakai lagi
    private const CACHE_PREFIX = 'html_sanitized:v1:';

    private static ?HTMLPurifier $purifier = null;

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        if (strlen($html) > self::MAX_CACHEABLE_BYTES) {
            return self::purifier()->purify($html);
        }

        return Cache::rememberForever(self::CACHE_PREFIX.sha1($html), fn () => self::purifier()->purify($html));
    }

    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier === null) {
            $cachePath = storage_path('framework/cache/purifier');
            if (! is_dir($cachePath)) {
                @mkdir($cachePath, 0755, true);
            }

            $config = HTMLPurifier_Config::createDefault();
            $config->set('Cache.SerializerPath', is_dir($cachePath) && is_writable($cachePath) ? $cachePath : null);
            $config->set('HTML.Allowed', implode(',', [
                'p[style]', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup',
                'h1[style]', 'h2[style]', 'h3[style]', 'h4[style]', 'h5[style]', 'h6[style]',
                'ul[style]', 'ol[style]', 'li[style]', 'blockquote[style]', 'pre', 'code', 'hr',
                'a[href|title|target|rel]',
                'img[src|alt|width|height|style]',
                'table[border|cellpadding|cellspacing|width|style]', 'thead', 'tbody', 'tfoot',
                'tr', 'th[colspan|rowspan|style]', 'td[colspan|rowspan|style]',
                'span[style]', 'div[style]',
                'iframe[src|width|height]',
            ]));
            $config->set('CSS.AllowedProperties', [
                'text-align', 'color', 'background-color', 'font-weight', 'font-style', 'text-decoration',
                'padding-left', 'margin-left', 'width', 'height', 'border-collapse', 'border', 'border-style',
                'border-width', 'border-color',
            ]);
            $config->set('HTML.SafeIframe', true);
            $config->set('URI.SafeIframeRegexp', '%^https://(www\.youtube\.com/embed/|www\.youtube-nocookie\.com/embed/|player\.vimeo\.com/video/)%');
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            $config->set('HTML.TargetNoopener', true);
            // Gambar yang ditempel langsung di editor tersimpan sebagai data URI (hanya jpeg/png/gif, hanya untuk img)
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'data' => true]);

            self::$purifier = new HTMLPurifier($config);
        }

        return self::$purifier;
    }
}

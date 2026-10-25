<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_removes_scripts_event_handlers_and_javascript_links(): void
    {
        $dirty = '<p onclick="x()">Halo</p><script>alert(1)</script><img src="x.png" onerror="alert(1)">'
            .'<a href="javascript:alert(1)">klik</a><iframe src="https://evil.example/x"></iframe>';

        $clean = HtmlSanitizer::clean($dirty);

        $this->assertStringContainsString('Halo', $clean);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('evil.example', $clean);
    }

    public function test_keeps_normal_editor_content(): void
    {
        $html = '<h2>Judul</h2><p style="text-align: center;"><strong>Tebal</strong> dan <em>miring</em></p>'
            .'<ul><li>Satu</li></ul><a href="https://example.com/a" target="_blank">tautan</a>'
            .'<img src="https://example.com/a.png" alt="gambar">'
            .'<table border="1"><tbody><tr><td>Sel</td></tr></tbody></table>';

        $clean = HtmlSanitizer::clean($html);

        foreach (['<h2>Judul</h2>', '<strong>Tebal</strong>', '<em>miring</em>', '<li>Satu</li>', 'https://example.com/a', 'alt="gambar"', '<td>Sel</td>', 'text-align:center'] as $needle) {
            $this->assertStringContainsString($needle, str_replace('text-align: center', 'text-align:center', $clean));
        }
    }

    public function test_keeps_youtube_iframe_and_pasted_png_data_image(): void
    {
        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';
        $clean = HtmlSanitizer::clean('<iframe src="https://www.youtube.com/embed/abc123" width="560" height="315"></iframe><img src="'.$png.'" alt="">');

        $this->assertStringContainsString('youtube.com/embed/abc123', $clean);
        $this->assertStringContainsString('data:image/png;base64', $clean);
    }

    public function test_null_and_blank_become_empty_string(): void
    {
        $this->assertSame('', HtmlSanitizer::clean(null));
        $this->assertSame('', HtmlSanitizer::clean('   '));
    }

    public function test_cache_key_is_versioned(): void
    {
        $html = '<p>Versi cache</p>';

        HtmlSanitizer::clean($html);

        $this->assertTrue(Cache::has('html_sanitized:v1:'.sha1($html)));
        $this->assertFalse(Cache::has('html_sanitized:'.sha1($html)));
    }
}

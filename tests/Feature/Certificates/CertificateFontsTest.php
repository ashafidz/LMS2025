<?php

namespace Tests\Feature\Certificates;

use App\Support\CertificateFonts;
use Tests\TestCase;

class CertificateFontsTest extends TestCase
{
    public function test_registry_has_the_five_fonts(): void
    {
        $this->assertSame(
            ['helvetica', 'times', 'montserrat', 'playfair', 'greatvibes'],
            CertificateFonts::keys()
        );
        $this->assertSame('Montserrat', CertificateFonts::label('montserrat'));
    }

    public function test_every_bundled_font_file_and_license_exists(): void
    {
        foreach (CertificateFonts::keys() as $key) {
            foreach (CertificateFonts::files($key) as $file) {
                $this->assertFileExists($file, "Font $key: file hilang");
                $this->assertGreaterThan(10000, filesize($file));
            }
        }
        $this->assertFileExists(public_path('fonts/certificates/OFL.txt'));
    }

    public function test_bold_support_flags(): void
    {
        $this->assertTrue(CertificateFonts::supportsBold('helvetica'));
        $this->assertTrue(CertificateFonts::supportsBold('times'));
        $this->assertTrue(CertificateFonts::supportsBold('montserrat'));
        $this->assertFalse(CertificateFonts::supportsBold('playfair'));
        $this->assertFalse(CertificateFonts::supportsBold('greatvibes'));
    }

    public function test_font_face_css_uses_file_path_for_pdf_and_url_for_browser(): void
    {
        $pdf = CertificateFonts::fontFaceCss(forPdf: true);
        $browser = CertificateFonts::fontFaceCss(forPdf: false);

        $this->assertStringContainsString(public_path('fonts/certificates/greatvibes.ttf'), $pdf);
        $this->assertStringContainsString('/fonts/certificates/greatvibes.ttf', $browser);
        $this->assertStringNotContainsString(public_path(), $browser);
        $this->assertStringNotContainsString('Helvetica', $pdf);
    }

    public function test_unknown_font_falls_back_to_helvetica(): void
    {
        $this->assertSame('Helvetica', CertificateFonts::cssFamily('tidak-ada'));
    }
}

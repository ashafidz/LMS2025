<?php

namespace Tests\Feature\Certificates;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Services\CertificateRenderer;
use App\Support\CertificateFonts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class CertificateRendererTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private CertificateRenderer $renderer;

    private $student;

    private $course;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->renderer = app(CertificateRenderer::class);

        $instructor = $this->makeInstructor(['name' => 'Bu Instruktur']);
        $this->student = $this->makeStudent(['name' => 'Siti Aminah', 'timezone' => 'Asia/Jakarta']);
        $this->course = $this->makeCourse($instructor, ['title' => 'Belajar Laravel']);
    }

    private function certificate(string $issuedAtUtc = '2026-10-23 20:00:00'): Certificate
    {
        return Certificate::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'certificate_code' => 'LMS-1-2-1700000000',
            'issued_at' => $issuedAtUtc,
        ]);
    }

    private function template(array $elements, string $orientation = 'landscape'): CertificateTemplate
    {
        Storage::disk('public')->put('certificate-templates/bg.png', UploadedFile::fake()->image('bg.png', 1200, 850)->get());

        return CertificateTemplate::factory()->create([
            'background_path' => 'certificate-templates/bg.png',
            'orientation' => $orientation,
            'elements' => $elements,
        ]);
    }

    private function el(string $key, array $override = []): array
    {
        return array_merge([
            'key' => $key, 'x' => 50, 'y' => 40, 'width' => 70, 'align' => 'center', 'font' => 'helvetica',
            'size' => 24, 'color' => '#112233', 'bold' => false, 'italic' => false, 'shrink_to_fit' => false,
        ], $override);
    }

    public function test_html_contains_all_certificate_data_with_indonesian_date_in_student_timezone(): void
    {
        $template = $this->template([
            $this->el('student_name'), $this->el('course_title', ['y' => 50]), $this->el('instructor_name', ['y' => 60]),
            $this->el('certificate_code', ['y' => 70]), $this->el('issued_date', ['y' => 80, 'date_format' => 'long']),
            $this->el('custom_text', ['y' => 90, 'text' => 'Diberikan <b>dengan bangga</b>']),
        ]);

        $html = $this->renderer->html($this->certificate(), $template);

        foreach (['Siti Aminah', 'Belajar Laravel', 'Bu Instruktur', 'LMS-1-2-1700000000', '24 Oktober 2026'] as $needle) {
            $this->assertStringContainsString($needle, $html);
        }
        $this->assertStringContainsString('Diberikan &lt;b&gt;dengan bangga&lt;/b&gt;', $html);
        $this->assertStringContainsString('color: #112233', $html);
    }

    public function test_short_date_format(): void
    {
        $template = $this->template([$this->el('student_name'), $this->el('issued_date', ['date_format' => 'short'])]);

        $this->assertStringContainsString('24/10/2026', $this->renderer->html($this->certificate(), $template));
    }

    public function test_position_math_for_left_center_and_right_alignment_on_landscape(): void
    {
        $template = $this->template([
            $this->el('student_name', ['x' => 10, 'y' => 10, 'width' => 40, 'align' => 'left']),
            $this->el('course_title', ['x' => 50, 'y' => 20, 'width' => 70, 'align' => 'center']),
            $this->el('instructor_name', ['x' => 90, 'y' => 30, 'width' => 40, 'align' => 'right']),
        ]);

        $html = $this->renderer->html($this->certificate(), $template);

        $this->assertStringContainsString('left: 29.70mm; top: 21.00mm; width: 118.80mm', $html);
        $this->assertStringContainsString('left: 44.55mm; top: 42.00mm; width: 207.90mm', $html);
        $this->assertStringContainsString('left: 148.50mm; top: 63.00mm; width: 118.80mm', $html);
    }

    public function test_portrait_uses_210_by_297_page(): void
    {
        $template = $this->template([$this->el('student_name', ['x' => 50, 'y' => 50, 'width' => 100, 'align' => 'center'])], 'portrait');

        $html = $this->renderer->html($this->certificate(), $template);

        $this->assertStringContainsString('width: 210mm; height: 297mm', $html);
        $this->assertStringContainsString('left: 0.00mm; top: 148.50mm; width: 210.00mm', $html);
    }

    public function test_fit_font_size_shrinks_long_text_but_not_below_sixty_percent(): void
    {
        $short = $this->renderer->fitFontSize('Ani', 'helvetica', false, 40, 500);
        $long = $this->renderer->fitFontSize('Nama Siswa Contoh Yang Cukup Panjang Sekali Untuk Diuji', 'helvetica', true, 40, 300);
        $huge = $this->renderer->fitFontSize(str_repeat('Panjang ', 40), 'helvetica', false, 40, 100);

        $this->assertSame(40, $short);
        $this->assertLessThan(40, $long);
        $this->assertGreaterThanOrEqual(24, $long);
        $this->assertSame(24, $huge);
    }

    public function test_fit_font_size_works_for_bundled_ttf_fonts(): void
    {
        $size = $this->renderer->fitFontSize('Nama Siswa Contoh Yang Cukup Panjang Sekali Untuk Diuji', 'greatvibes', false, 40, 300);

        $this->assertLessThan(40, $size);
        $this->assertGreaterThanOrEqual(24, $size);
    }

    public function test_html_applies_shrink_to_fit_only_when_enabled(): void
    {
        $this->student->update(['name' => 'Nama Siswa Contoh Yang Cukup Panjang Sekali Untuk Diuji Bersama']);
        $with = $this->template([$this->el('student_name', ['size' => 40, 'width' => 30, 'shrink_to_fit' => true])]);
        $without = $this->template([$this->el('student_name', ['size' => 40, 'width' => 30, 'shrink_to_fit' => false])]);

        $certificate = $this->certificate();

        $this->assertStringNotContainsString('font-size: 40pt', $this->renderer->html($certificate, $with));
        $this->assertStringContainsString('font-size: 40pt', $this->renderer->html($certificate, $without));
    }

    public function test_pdf_without_template_uses_legacy_design(): void
    {
        $certificate = $this->certificate();

        $output = $this->renderer->pdf($certificate, null)->output();

        $this->assertStringStartsWith('%PDF', $output);
        $this->assertStringContainsString('legacy', $this->renderer->cacheKey($certificate, null));
    }

    public function test_sample_pdf_renders_with_every_bundled_font(): void
    {
        $elements = [];
        foreach (CertificateFonts::keys() as $i => $font) {
            $elements[] = $this->el($i === 0 ? 'student_name' : 'custom_text', [
                'font' => $font, 'y' => 10 + $i * 15, 'text' => 'Contoh '.$font, 'bold' => true, 'italic' => true,
            ]);
        }
        $template = $this->template($elements);

        $output = $this->renderer->samplePdf($template)->output();

        $this->assertStringStartsWith('%PDF', $output);
        preg_match_all('/\/BaseFont\s*\/([A-Za-z0-9+\-_]+)/', $output, $m);
        $embedded = implode(' ', $m[1]);
        // Nama font ikut tertanam; Montserrat harus Regular/Bold, bukan bobot bawaan file variable (Thin)
        foreach (['Helvetica-BoldOblique', 'Times-BoldItalic', 'Montserrat-Bold', 'PlayfairDisplay-Regular', 'GreatVibes-Regular'] as $name) {
            $this->assertStringContainsString($name, $embedded);
        }
        $this->assertStringNotContainsString('Thin', $embedded);
    }

    public function test_cache_key_changes_with_template_version_and_certificate_data(): void
    {
        $certificate = $this->certificate();
        $a = $this->template([$this->el('student_name')]);
        $b = $this->template([$this->el('student_name')]);

        $keyA = $this->renderer->cacheKey($certificate, $a);
        $keyB = $this->renderer->cacheKey($certificate, $b);

        $this->assertNotSame($keyA, $keyB);
        $this->assertSame($keyA, $this->renderer->cacheKey($certificate, $a));

        $this->student->update(['name' => 'Nama Baru']);
        $this->assertNotSame($keyA, $this->renderer->cacheKey($certificate->fresh(), $a));
    }

    public function test_preview_html_places_text_by_percent(): void
    {
        $template = $this->template([$this->el('student_name', ['x' => 25, 'y' => 40, 'width' => 50, 'align' => 'left'])]);

        $html = $this->renderer->previewHtml($this->certificate(), $template);

        $this->assertStringContainsString('Siti Aminah', $html);
        $this->assertStringContainsString('left: 25.00%; top: 40.00%; width: 50.00%', $html);
    }

    public function test_bold_and_italic_are_ignored_for_fonts_without_those_faces(): void
    {
        $template = $this->template([
            $this->el('student_name', ['font' => 'greatvibes', 'bold' => true, 'italic' => true]),
            $this->el('course_title', ['font' => 'montserrat', 'bold' => true, 'italic' => true]),
            $this->el('instructor_name', ['font' => 'helvetica', 'bold' => true, 'italic' => true]),
        ]);

        $elements = $this->renderer->resolveElements($template, [
            'student_name' => 'A', 'course_title' => 'B', 'instructor_name' => 'C',
        ]);

        $this->assertSame([false, false], [$elements[0]['bold'], $elements[0]['italic']]);
        $this->assertSame([true, false], [$elements[1]['bold'], $elements[1]['italic']]);
        $this->assertSame([true, true], [$elements[2]['bold'], $elements[2]['italic']]);
    }

    public function test_preview_canvas_works_without_aspect_ratio_and_container_units(): void
    {
        $certificate = $this->certificate();
        $landscape = $this->template([$this->el('student_name', ['size' => 24])]);
        $html = $this->renderer->previewHtml($certificate, $landscape);

        // Tinggi kanvas dari padding-top (semua browser), bukan aspect-ratio (Safari 15+)
        $this->assertStringContainsString('height: 0; padding-top: 70.7071%;', $html);
        $this->assertStringNotContainsString('aspect-ratio', $html);
        // Ukuran huruf cadangan untuk browser tanpa cqw (Safari < 16, Chrome < 105) ditulis SEBELUM nilai cqw
        $this->assertMatchesRegularExpression('/font-size: calc\(min\(800px, 100vw - 64px\) \* 0\.02851\); font-size: 2\.851cqw;/', $html);

        $portrait = $this->template([$this->el('student_name')]);
        $portrait->update(['orientation' => 'portrait']);
        $this->assertStringContainsString('padding-top: 141.4286%;', $this->renderer->previewHtml($certificate, $portrait->fresh()));
    }
}

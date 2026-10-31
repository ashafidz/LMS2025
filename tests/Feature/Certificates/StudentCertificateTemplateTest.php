<?php

namespace Tests\Feature\Certificates;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\CourseReview;
use App\Services\CertificateTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class StudentCertificateTemplateTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private $student;

    private $course;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $this->student = $this->makeStudent(['name' => 'Siti Aminah']);
        $this->course = $this->makeCourse($this->makeInstructor(), ['title' => 'Belajar Laravel']);
        $lesson = $this->makeArticleLesson($this->makeModule($this->course));
        $this->enroll($this->student, $this->course);
        $this->student->completedLessons()->attach($lesson->id);
        CourseReview::create(['course_id' => $this->course->id, 'user_id' => $this->student->id, 'rating' => 5, 'comment' => 'Bagus']);
    }

    private function activeTemplate(): CertificateTemplate
    {
        Storage::disk('public')->put('certificate-templates/bg.png', UploadedFile::fake()->image('bg.png', 1200, 850)->get());
        $template = CertificateTemplate::factory()->create(['background_path' => 'certificate-templates/bg.png']);
        app(CertificateTemplateService::class)->activate($template, $this->makeAdmin());

        return $template->fresh();
    }

    private function cachedFiles(): array
    {
        return Storage::disk('local')->files('certificates');
    }

    public function test_download_without_active_template_uses_legacy_design_and_cache(): void
    {
        $this->actingAs($this->student)->get(route('student.certificate.download', $this->course))->assertOk();

        $files = $this->cachedFiles();
        $this->assertCount(1, $files);
        $this->assertStringContainsString('-legacy-', $files[0]);
    }

    public function test_download_with_active_template_uses_it(): void
    {
        $template = $this->activeTemplate();

        $this->actingAs($this->student)->get(route('student.certificate.download', $this->course))->assertOk();

        $files = $this->cachedFiles();
        $this->assertCount(1, $files);
        $this->assertStringContainsString("-t{$template->id}v{$template->version}-", $files[0]);
    }

    public function test_activating_another_version_regenerates_and_drops_the_old_cached_file(): void
    {
        $first = $this->activeTemplate();
        $this->actingAs($this->student)->get(route('student.certificate.download', $this->course))->assertOk();
        $certificate = Certificate::where('user_id', $this->student->id)->firstOrFail();
        $firstFile = $this->cachedFiles()[0];

        $second = CertificateTemplate::factory()->create(['background_path' => 'certificate-templates/bg.png']);
        app(CertificateTemplateService::class)->activate($second, $this->makeAdmin());
        $this->actingAs($this->student)->get(route('student.certificate.download', $this->course))->assertOk();

        $files = $this->cachedFiles();
        $this->assertCount(1, $files);
        $this->assertNotSame($firstFile, $files[0]);
        $this->assertStringContainsString("-t{$second->id}v{$second->version}-", $files[0]);

        $same = Certificate::where('user_id', $this->student->id)->firstOrFail();
        $this->assertSame($certificate->certificate_code, $same->certificate_code);
        $this->assertTrue($certificate->issued_at->equalTo($same->issued_at));
        $this->assertSame(1, Certificate::count());
        $this->assertNotNull($first);
    }

    public function test_second_download_of_same_version_reuses_cached_file(): void
    {
        $this->activeTemplate();

        $this->actingAs($this->student)->get(route('student.certificate.download', $this->course))->assertOk();
        $file = $this->cachedFiles()[0];
        $modified = Storage::disk('local')->lastModified($file);
        $this->actingAs($this->student)->get(route('student.certificate.download', $this->course))->assertOk();

        $this->assertSame([$file], $this->cachedFiles());
        $this->assertSame($modified, Storage::disk('local')->lastModified($file));
    }

    public function test_preview_with_active_template_shows_the_visual_certificate_and_download_button(): void
    {
        $this->activeTemplate();

        $response = $this->actingAs($this->student)->getJson(route('student.certificate.preview', $this->course))->assertOk();

        $html = $response->json('html');
        $this->assertStringContainsString('certificate-canvas', $html);
        $this->assertStringContainsString('Siti Aminah', $html);
        $this->assertStringContainsString(route('student.certificate.download', $this->course), $html);
    }

    public function test_preview_without_template_keeps_the_old_card(): void
    {
        $response = $this->actingAs($this->student)->getJson(route('student.certificate.preview', $this->course))->assertOk();

        $this->assertStringContainsString('Sertifikat Kelulusan Siap Diunduh', $response->json('html'));
        $this->assertStringNotContainsString('certificate-canvas', $response->json('html'));
    }

    public function test_student_who_is_not_eligible_is_still_refused(): void
    {
        $this->activeTemplate();
        $other = $this->makeStudent();
        $this->enroll($other, $this->course);

        $this->actingAs($other)->getJson(route('student.certificate.preview', $this->course))->assertForbidden();
        $this->actingAs($other)->get(route('student.certificate.download', $this->course))->assertSessionHas('error');
        $this->assertSame(0, Certificate::where('user_id', $other->id)->count());
    }

    public function test_cleanup_removes_only_old_files_of_the_same_certificate(): void
    {
        $this->activeTemplate();
        $this->actingAs($this->student)->get(route('student.certificate.download', $this->course))->assertOk();
        $code = Certificate::where('user_id', $this->student->id)->value('certificate_code');

        // PDF lama sertifikat ini, PDF milik sertifikat lain, dan file yang kodenya hanya mirip awalannya
        Storage::disk('local')->put("certificates/{$code}-t99v99-oldhash.pdf", 'lama');
        Storage::disk('local')->put('certificates/LMS-OTHER-1-oldhash.pdf', 'orang lain');
        Storage::disk('local')->put("certificates/{$code}9-lookalike.pdf", 'kode mirip');

        $second = CertificateTemplate::factory()->create(['background_path' => 'certificate-templates/bg.png']);
        app(CertificateTemplateService::class)->activate($second, $this->makeAdmin());
        $this->actingAs($this->student)->get(route('student.certificate.download', $this->course))->assertOk();

        Storage::disk('local')->assertMissing("certificates/{$code}-t99v99-oldhash.pdf");
        Storage::disk('local')->assertExists('certificates/LMS-OTHER-1-oldhash.pdf');
        Storage::disk('local')->assertExists("certificates/{$code}9-lookalike.pdf");
        $mine = array_filter($this->cachedFiles(), fn ($f) => str_starts_with($f, "certificates/{$code}-"));
        $this->assertCount(1, $mine);
    }

    public function test_course_page_loads_the_old_browser_font_fallback_and_preview_carries_font_fractions(): void
    {
        $this->activeTemplate();

        $this->actingAs($this->student)->get(route('student.courses.show', $this->course->slug))
            ->assertOk()
            ->assertSee('js/certificate-canvas-fallback.js', false)
            ->assertSee('fitCertificateCanvas(lessonContentEl)', false);

        $html = $this->actingAs($this->student)->getJson(route('student.certificate.preview', $this->course))->json('html');
        $this->assertMatchesRegularExpression('/data-font-fraction="0\.\d{5}"/', $html);
    }

    public function test_certificate_index_shows_warning_and_continue_button_when_course_has_new_lessons(): void
    {
        // Berikan sertifikat ke siswa
        Certificate::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'certificate_code' => 'LMS-'.$this->course->id.'-'.$this->student->id.'-123456',
            'issued_at' => now(),
        ]);

        // Instruktur menambahkan pelajaran baru sehingga progres tidak lagi 100%
        $this->makeArticleLesson($this->course->modules->first());

        $response = $this->actingAs($this->student)->get(route('student.certificates.index'));
        $response->assertOk();
        $response->assertSee('Kursus Diperbarui', false);
        $response->assertSee('Lanjutkan Kursus', false);
        $response->assertSee(route('student.courses.show', $this->course), false);

        // Jika mencoba unduh langsung, diarahkan kembali dengan pesan error spesifik
        $downloadResponse = $this->actingAs($this->student)->get(route('student.certificate.download', $this->course));
        $downloadResponse->assertRedirect();
        $downloadResponse->assertSessionHas('error', 'Kursus telah diperbarui dengan materi baru. Silakan selesaikan seluruh pelajaran untuk mengunduh sertifikat kembali.');
    }

    public function test_certificate_index_shows_download_button_when_all_lessons_are_completed(): void
    {
        Certificate::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'certificate_code' => 'LMS-'.$this->course->id.'-'.$this->student->id.'-123456',
            'issued_at' => now(),
        ]);

        $response = $this->actingAs($this->student)->get(route('student.certificates.index'));
        $response->assertOk();
        $response->assertDontSee('Kursus Diperbarui', false);
        $response->assertSee('Unduh Sertifikat', false);
        $response->assertSee(route('student.certificate.download', $this->course->id), false);
    }
}

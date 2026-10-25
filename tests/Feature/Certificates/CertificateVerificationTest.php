<?php

namespace Tests\Feature\Certificates;

use App\Models\Certificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * Halaman publik untuk mengecek keaslian sertifikat dari kodenya (tanpa login).
 */
class CertificateVerificationTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private function certificate(array $student = [], array $course = []): Certificate
    {
        $instructor = $this->makeInstructor(['name' => 'Ibu Rina']);
        $student = $this->makeStudent(array_merge(['name' => 'Siti Aminah', 'email' => 'siti@example.test', 'timezone' => 'Asia/Jakarta'], $student));
        $course = $this->makeCourse($instructor, array_merge(['title' => 'Belajar Laravel'], $course));

        return Certificate::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'certificate_code' => 'LMS-12-34-1761400000',
            // 23 Oktober 20:00 UTC = 24 Oktober 03:00 WIB, sama dengan yang tercetak di sertifikat
            'issued_at' => '2026-10-23 20:00:00',
        ]);
    }

    public function test_form_is_public(): void
    {
        $this->get(route('certificates.verify'))
            ->assertOk()
            ->assertSee('Cek Keaslian Sertifikat')
            ->assertSee('name="kode"', false);
    }

    public function test_valid_code_shows_holder_course_and_issue_date(): void
    {
        $this->certificate();

        $this->get(route('certificates.verify', ['kode' => 'LMS-12-34-1761400000']))
            ->assertOk()
            ->assertSee('Sertifikat asli')
            ->assertSee('Siti Aminah')
            ->assertSee('Belajar Laravel')
            ->assertSee('Ibu Rina')
            ->assertSee('24 Oktober 2026')
            ->assertSee('LMS-12-34-1761400000')
            ->assertDontSee('siti@example.test');
    }

    public function test_code_with_surrounding_spaces_still_matches(): void
    {
        $this->certificate();

        $this->get(route('certificates.verify', ['kode' => '  LMS-12-34-1761400000  ']))
            ->assertOk()->assertSee('Sertifikat asli');
    }

    public function test_unknown_code_is_reported_without_details(): void
    {
        $this->certificate();

        $this->get(route('certificates.verify', ['kode' => 'LMS-99-99-1']))
            ->assertOk()
            ->assertSee('Kode sertifikat tidak ditemukan')
            ->assertDontSee('Siti Aminah')
            ->assertDontSee('Sertifikat asli');
    }

    public function test_overly_long_input_is_treated_as_not_found(): void
    {
        $this->get(route('certificates.verify', ['kode' => str_repeat('A', 500)]))
            ->assertOk()
            ->assertSee('Kode sertifikat tidak ditemukan');
    }

    public function test_certificate_of_a_deleted_course_is_still_valid(): void
    {
        $certificate = $this->certificate();
        $certificate->course->delete();

        $this->get(route('certificates.verify', ['kode' => 'LMS-12-34-1761400000']))
            ->assertOk()
            ->assertSee('Sertifikat asli')
            ->assertSee('Belajar Laravel');
    }

    public function test_lookups_are_rate_limited(): void
    {
        $statuses = [];
        foreach (range(1, 31) as $i) {
            $statuses[] = $this->get(route('certificates.verify', ['kode' => 'TEBAK-'.$i]))->status();
        }

        $this->assertSame(429, end($statuses));
    }

    public function test_public_footer_links_to_the_verification_page(): void
    {
        $this->get(route('faqs'))->assertOk()->assertSee(route('certificates.verify'), false);
    }
}

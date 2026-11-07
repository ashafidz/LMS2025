<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class IllustrationTest extends TestCase
{
    use RefreshDatabase;

    private const NAMES = [
        'rs-01-hero-belajar', 'rs-02-tentang-tim', 'rs-03-masuk', 'rs-04-daftar', 'rs-05-lupa-sandi',
        'rs-06-verifikasi-email', 'rs-07-cari-kursus', 'rs-08-semua-selesai', 'rs-09-sertifikat-kosong',
        'rs-10-transaksi-kosong', 'rs-11-keranjang-kosong', 'rs-12-instruktur-mulai', 'rs-13-kuis-lulus',
        'rs-14-kuis-coba-lagi', 'rs-15-sertifikat-terbit', 'rs-16-bayar-berhasil', 'rs-17-bayar-menunggu',
        'rs-18-bayar-gagal', 'rs-19-status-akun', 'rs-20-tersesat',
    ];

    public function test_component_renders_a_decorative_image_with_its_size(): void
    {
        $html = Blade::render('<x-rs.illustration name="rs-07-cari-kursus" />');

        $this->assertStringContainsString('images/illustrations/rs-07-cari-kursus.webp"', $html);
        $this->assertStringContainsString('alt=""', $html);
        $this->assertStringContainsString('width="385"', $html);
        $this->assertStringContainsString('height="560"', $html);
        $this->assertStringContainsString('rs-illu--md', $html);
    }

    public function test_component_passes_extra_attributes_and_size(): void
    {
        $html = Blade::render('<x-rs.illustration name="rs-01-hero-belajar" size="lg" loading="eager" fetchpriority="high" />');

        $this->assertStringContainsString('rs-illu--lg', $html);
        $this->assertStringContainsString('fetchpriority="high"', $html);
        $this->assertStringContainsString('loading="eager"', $html);
        // The browser uses the first "loading" attribute, so the default must be replaced, not repeated.
        $this->assertSame(1, substr_count($html, 'loading='));
    }

    public function test_unknown_name_renders_nothing(): void
    {
        $this->assertStringNotContainsString('<img', Blade::render('<x-rs.illustration name="rs-99-tidak-ada" />'));
    }

    public function test_every_illustration_is_known_to_the_component_and_exists_on_disk(): void
    {
        foreach (self::NAMES as $name) {
            $this->assertFileExists(public_path('images/illustrations/'.$name.'.webp'));
            $this->assertStringContainsString('<img', Blade::render('<x-rs.illustration name="'.$name.'" />'), $name);
        }

        $onDisk = collect(glob(public_path('images/illustrations/*.webp')))->map(fn ($f) => basename($f, '.webp'))->sort()->values()->all();
        $this->assertSame($onDisk, collect(self::NAMES)->sort()->values()->all(), 'Exactly 20 files (licence limit, ADR-013).');
    }

    public function test_login_page_shows_its_illustration(): void
    {
        $this->get('/login')->assertOk()->assertSee('rs-03-masuk.webp', false);
    }

    public function test_not_found_page_shows_its_illustration(): void
    {
        $this->get('/halaman-yang-tidak-ada')->assertNotFound()->assertSee('rs-20-tersesat.webp', false);
    }

    public function test_student_without_enrolment_sees_the_empty_illustration(): void
    {
        $this->actingAs($this->makeStudent())->withSession(['active_role' => 'student'])
            ->get(route('student.dashboard'))->assertOk()->assertSee('rs-07-cari-kursus.webp', false);
    }

    public function test_empty_cart_shows_its_illustration(): void
    {
        $this->actingAs($this->makeStudent())->withSession(['active_role' => 'student'])
            ->get(route('student.cart.index'))->assertOk()->assertSee('rs-11-keranjang-kosong.webp', false);
    }

    public function test_empty_student_pages_show_their_illustrations(): void
    {
        $student = $this->makeStudent();
        $this->actingAs($student)->withSession(['active_role' => 'student']);

        $this->get(route('student.certificates.index'))->assertOk()->assertSee('rs-09-sertifikat-kosong.webp', false);
        $this->get(route('student.transactions.index'))->assertOk()->assertSee('rs-10-transaksi-kosong.webp', false);
    }
}

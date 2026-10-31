<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();

        SiteSetting::create(['site_name' => 'Ruang Studi Uji']);
        cache()->forget('site_settings');
    }

    public function test_public_pages_render_with_new_layout(): void
    {
        foreach (['/', '/courses', '/about', '/contact', '/faqs', '/testimonials', '/detail-service'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('class="rs-header"', false)
                ->assertSee('ruang-studi.css', false)
                ->assertDontSee('aos.js', false)
                ->assertDontSee('main.css', false)
                ->assertDontSee('bootstrap.min.css', false)
                ->assertDontSee('BootstrapMade')
                ->assertDontSee('home-page/', false);
        }
    }

    public function test_public_layout_loads_literata_serif_font_only(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('family=Literata', false)
            ->assertDontSee('Atkinson', false);
    }

    public function test_service_detail_uses_the_new_illustrations(): void
    {
        $page = $this->get('/detail-service')->assertOk();

        foreach (['pelatihan-interaktif', 'kelas-beragam', 'materi-terbaru', 'evaluasi-sertifikat'] as $name) {
            $page->assertSee('images/layanan/'.$name.'.jpg', false);
            $this->assertFileExists(public_path('images/layanan/'.$name.'.jpg'));
        }

        $page->assertDontSee('images/layanan1.jpg', false);
    }

    public function test_home_shows_about_and_catalog_calls_to_action(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('class="rs-cta"', false)
            ->assertSee('href="'.route('about').'"', false)
            ->assertSeeInOrder(['Kenali Lebih Dekat', 'Tentang Kami', 'Kursus yang Sering Dibeli', 'Cari Kursus Lainnya?', 'Lihat Semua Kursus']);
    }

    public function test_home_and_about_use_the_new_illustrations(): void
    {
        $this->get('/')->assertOk()->assertSee('images/tentang/beranda-tentang.jpg', false);

        $about = $this->get(route('about'))->assertOk();
        foreach (['akses-kapan-saja', 'instruktur', 'karier'] as $name) {
            $about->assertSee('images/tentang/'.$name.'.jpg', false);
        }

        foreach (['beranda-tentang', 'akses-kapan-saja', 'instruktur', 'karier'] as $name) {
            $this->assertFileExists(public_path('images/tentang/'.$name.'.jpg'));
        }
    }

    public function test_home_without_data_shows_honest_empty_states(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Belum ada testimoni')
            ->assertDontSee('0+ Siswa');
    }

    public function test_home_lists_popular_course_with_price(): void
    {
        $course = Course::factory()->create(['title' => 'Belajar Dasar Pemrograman', 'price' => 150000]);

        $this->get('/')
            ->assertOk()
            ->assertSee($course->title)
            ->assertSee('Rp150.000');
    }

    public function test_catalog_search_and_category_filter_still_work(): void
    {
        $catA = CourseCategory::factory()->create();
        $catB = CourseCategory::factory()->create();
        $courseA = Course::factory()->create(['category_id' => $catA->id, 'title' => 'Alpha Matematika Dasar']);
        $courseB = Course::factory()->create(['category_id' => $catB->id, 'title' => 'Beta Bahasa Inggris']);

        // "Kursus Terpopuler" (id="pricing") is always shown below the results, so only check the results part.
        $results = fn (string $url): string => explode('id="pricing"', $this->get($url)->assertOk()->getContent())[0];

        $html = $results('/courses?search=Alpha');
        $this->assertStringContainsString($courseA->title, $html);
        $this->assertStringNotContainsString($courseB->title, $html);

        $html = $results('/courses?categories[]='.$catB->id);
        $this->assertStringContainsString($courseB->title, $html);
        $this->assertStringNotContainsString($courseA->title, $html);

        $this->get('/courses')
            ->assertSee('name="search"', false)
            ->assertSee('name="categories[]"', false);

        $this->get('/courses?search=zzzzqqqq')
            ->assertOk()
            ->assertSee('Kursus tidak ditemukan');
    }

    public function test_catalog_shows_diamond_price_not_free(): void
    {
        Course::factory()->create([
            'payment_type' => 'diamonds',
            'diamond_price' => 250,
            'price' => 0,
        ]);

        $this->get('/courses')
            ->assertOk()
            ->assertSee('250 Diamond');
    }

    public function test_course_detail_guest_sees_login_button(): void
    {
        $course = Course::factory()->create();

        $this->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertSee('Masuk untuk mendaftar')
            ->assertSee(route('login'), false);
    }

    public function test_course_detail_student_can_add_to_cart(): void
    {
        $course = Course::factory()->create();
        $student = $this->makeStudent();

        $this->actingAs($student)
            ->withSession(['active_role' => 'student'])
            ->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertSee(route('student.cart.add', $course), false)
            ->assertSee('Tambahkan ke keranjang');
    }

    public function test_course_detail_enrolled_student_sees_continue(): void
    {
        $course = Course::factory()->create();
        $student = $this->makeStudent();
        $this->enroll($student, $course);

        $this->actingAs($student)
            ->withSession(['active_role' => 'student'])
            ->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertSee('Lanjutkan Belajar')
            ->assertSee(route('student.courses.show', $course->slug), false);
    }

    public function test_course_detail_diamond_course_with_low_balance_is_disabled(): void
    {
        $course = Course::factory()->create([
            'payment_type' => 'diamonds',
            'diamond_price' => 50,
            'price' => 0,
        ]);
        $student = $this->makeStudent();
        $student->update(['diamond_balance' => 10]);

        $response = $this->actingAs($student)
            ->withSession(['active_role' => 'student'])
            ->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertSee(route('student.courses.purchase_with_diamonds', $course), false)
            ->assertSee('Diamond kamu belum cukup untuk kursus ini.');

        $this->assertMatchesRegularExpression(
            '/<button[^>]*\bdisabled\b[^>]*>\s*Beli dengan Diamond/',
            $response->getContent()
        );
    }

    public function test_contact_form_keeps_fields_and_ajax_hooks(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('action="'.route('contact.store').'"', false)
            ->assertSee('data-rs-contact', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="subject"', false)
            ->assertSee('name="message"', false)
            ->assertSee('data-rs-status="wait"', false)
            ->assertSee('data-rs-status="error"', false)
            ->assertSee('data-rs-status="ok"', false);
    }

    public function test_public_profile_shows_role_label(): void
    {
        $student = $this->makeStudent(['name' => 'Siti Pelajar']);

        $this->get(route('profile.show', $student->id))
            ->assertOk()
            ->assertSee('Siti Pelajar')
            ->assertSee('Siswa')
            ->assertDontSee('STUDENT');
    }
}

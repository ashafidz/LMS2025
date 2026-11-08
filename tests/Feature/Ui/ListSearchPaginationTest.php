<?php

namespace Tests\Feature\Ui;

use App\Models\ContactMessage;
use App\Models\CourseCategory;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

/**
 * Search box and advanced pagination on dashboard lists (one page per role and shape: plain table, relation search,
 * grouped search that must not leak rows, card grid, and a tabbed page).
 */
class ListSearchPaginationTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    public function test_contact_messages_page_size_numbers_and_search(): void
    {
        $superadmin = $this->makeSuperadmin();
        for ($i = 1; $i <= 30; $i++) {
            ContactMessage::create(['name' => "Pengirim $i", 'email' => "p$i@example.test", 'subject' => "Subjek $i", 'message' => 'Halo']);
        }
        ContactMessage::create(['name' => 'Rina Kartika', 'email' => 'rina@example.test', 'subject' => 'Tanya sertifikat', 'message' => 'Halo']);

        $this->actingAs($superadmin)->get(route('superadmin.contact-messages.index'))
            ->assertOk()
            ->assertSee('Menampilkan <strong>1-25</strong> dari <strong>31</strong> data', false)
            ->assertSee('aria-label="Halaman 2"', false)
            ->assertSee('placeholder="Cari nama, email, subjek, atau isi pesan..."', false);

        $this->actingAs($superadmin)->get(route('superadmin.contact-messages.index', ['per_page' => 10, 'page' => 2]))
            ->assertOk()
            ->assertSee('Menampilkan <strong>11-20</strong> dari <strong>31</strong> data', false);

        $this->actingAs($superadmin)->get(route('superadmin.contact-messages.index', ['search' => 'sertifikat']))
            ->assertOk()
            ->assertSee('Rina Kartika')
            ->assertDontSee('Pengirim 1<', false)
            ->assertSee('Menampilkan <strong>1-1</strong> dari <strong>1</strong> data', false);

        $this->actingAs($superadmin)->get(route('superadmin.contact-messages.index', ['per_page' => 999]))
            ->assertOk()
            ->assertSee('Menampilkan <strong>1-25</strong>', false);
    }

    public function test_category_search_and_role_check_unchanged(): void
    {
        CourseCategory::create(['name' => 'Kewarganegaraan', 'slug' => 'kewarganegaraan']);
        CourseCategory::create(['name' => 'Pemrograman Web', 'slug' => 'pemrograman-web']);

        $this->actingAs($this->makeAdmin())->get(route('admin.course-categories.index', ['search' => 'pemrogram']))
            ->assertOk()
            ->assertSee('Pemrograman Web')
            ->assertDontSee('Kewarganegaraan');

        $this->actingAs($this->makeStudent())->get(route('admin.course-categories.index'))
            ->assertForbidden();
    }

    public function test_enrollment_search_never_shows_already_enrolled_or_non_students(): void
    {
        $admin = $this->makeAdmin();
        $instructor = $this->makeInstructor(['name' => 'Budi Instruktur', 'email' => 'budi.guru@example.test']);
        $course = $this->makeCourse($instructor);
        $enrolled = $this->makeStudent(['name' => 'Budi Terdaftar', 'email' => 'budi.terdaftar@example.test']);
        $this->enroll($enrolled, $course);
        $this->makeStudent(['name' => 'Budi Baru', 'email' => 'budi.baru@example.test']);

        // "budi" matches all three by name and by email; only the not-yet-enrolled student may appear.
        $this->actingAs($admin)->get(route('admin.course-enrollments.create', [$course, 'search' => 'budi']))
            ->assertOk()
            ->assertSee('Budi Baru')
            ->assertDontSee('Budi Terdaftar')
            ->assertDontSee('Budi Instruktur');
    }

    public function test_student_my_courses_card_grid_uses_its_own_page_sizes_and_search(): void
    {
        $student = $this->makeStudent();
        $instructor = $this->makeInstructor();
        foreach (['Dasar Pancasila', 'Ketahanan Nasional', 'Hukum Tata Negara'] as $title) {
            $this->enroll($student, $this->makeCourse($instructor, ['title' => $title]));
        }

        $this->actingAs($student)->withSession(['active_role' => 'student'])
            ->get(route('student.my_courses', ['search' => 'nasional']))
            ->assertOk()
            ->assertSee('Ketahanan Nasional')
            ->assertDontSee('Dasar Pancasila');

        $this->actingAs($student)->withSession(['active_role' => 'student'])
            ->get(route('student.my_courses', ['search' => 'zzz']))
            ->assertOk()
            ->assertSee('Tidak ada kursus yang cocok dengan pencarian.')
            ->assertDontSee('Anda Belum Terdaftar di Kursus Apapun');
    }

    public function test_student_transactions_search_by_course_title(): void
    {
        $student = $this->makeStudent();
        $course = $this->makeCourse($this->makeInstructor(), ['title' => 'Kursus Sejarah Indonesia']);
        $order = Order::factory()->create(['user_id' => $student->id, 'status' => 'paid', 'final_amount' => 10000]);
        $order->items()->create(['course_id' => $course->id, 'price_at_purchase' => 10000]);
        $other = Order::factory()->create(['user_id' => $student->id, 'status' => 'paid', 'final_amount' => 5000]);

        $this->actingAs($student)->withSession(['active_role' => 'student'])
            ->get(route('student.transactions.index', ['search' => 'sejarah']))
            ->assertOk()
            ->assertSee($order->order_code)
            ->assertDontSee($other->order_code);
    }

    public function test_search_without_results_says_so_instead_of_belum_ada(): void
    {
        $superadmin = $this->makeSuperadmin();
        ContactMessage::create(['name' => 'Rina', 'email' => 'rina@example.test', 'subject' => 'Halo', 'message' => 'Halo']);

        $this->actingAs($superadmin)->get(route('superadmin.contact-messages.index', ['search' => 'zzz']))
            ->assertOk()
            ->assertSee('Tidak ada data yang cocok dengan pencarian.')
            ->assertDontSee('Belum ada pesan kontak yang masuk.');

        $this->actingAs($superadmin)->get(route('superadmin.contact-messages.index'))
            ->assertOk()
            ->assertSee('Rina');
    }

    public function test_page_past_the_end_offers_the_last_page(): void
    {
        $superadmin = $this->makeSuperadmin();
        for ($i = 1; $i <= 12; $i++) {
            ContactMessage::create(['name' => "Pengirim $i", 'email' => "p$i@example.test", 'subject' => 'S', 'message' => 'M']);
        }

        $this->actingAs($superadmin)->get(route('superadmin.contact-messages.index', ['per_page' => 10, 'page' => 9]))
            ->assertOk()
            ->assertSee('Halaman 9 kosong.')
            ->assertSee('Buka halaman terakhir (2)');
    }

    public function test_toolbar_drops_every_page_number_but_keeps_sort_and_size(): void
    {
        $html = $this->actingAs($this->makeSuperadmin())
            ->get(route('superadmin.contact-messages.index', ['page' => 3, 'diamond_page' => 2, 'per_page' => 50, 'sort' => 'nama', 'search' => 'x']))
            ->assertOk()->getContent();
        // Only the search form (the pager forms keep other lists' page numbers on purpose).
        preg_match('#<form[^>]*role="search".*?</form>#s', $html, $m);
        $form = $m[0] ?? '';

        $this->assertStringContainsString('<input type="hidden" name="per_page" value="50">', $form);
        $this->assertStringContainsString('<input type="hidden" name="sort" value="nama">', $form);
        $this->assertStringNotContainsString('name="diamond_page"', $form);
        $this->assertStringNotContainsString('name="page"', $form);
    }
}

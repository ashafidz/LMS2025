<?php

namespace Tests\Feature\Instructor;

use App\Models\CourseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class CourseThumbnailTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Kursus Uji',
            'category_id' => CourseCategory::factory()->create()->id,
            'description' => '<p>Deskripsi</p>',
            'availability_type' => 'lifetime',
            'payment_type' => 'money',
        ], $overrides);
    }

    public function test_too_big_thumbnail_is_rejected_and_the_create_page_explains_why(): void
    {
        Storage::fake('public');
        $owner = $this->makeInstructor();
        $createUrl = route('instructor.courses.create');

        $this->actingAs($owner)->withSession(['active_role' => 'instructor'])->from($createUrl)
            ->post(route('instructor.courses.store'), $this->payload([
                'thumbnail' => UploadedFile::fake()->image('besar.jpg', 100, 100)->size(3000),
            ]))
            ->assertRedirect($createUrl)
            ->assertSessionHasErrors(['thumbnail' => 'Ukuran thumbnail maksimal 2 MB.']);

        $this->actingAs($owner)->withSession(['active_role' => 'instructor'])->get($createUrl)
            ->assertOk()
            ->assertSee('Kursus belum tersimpan')
            ->assertSee('Ukuran thumbnail maksimal 2 MB.')
            ->assertSee('Pilih ulang gambar thumbnail');
    }

    public function test_missing_thumbnail_on_create_has_an_indonesian_message(): void
    {
        $this->actingAs($this->makeInstructor())->withSession(['active_role' => 'instructor'])
            ->post(route('instructor.courses.store'), $this->payload())
            ->assertSessionHasErrors(['thumbnail' => 'Pilih gambar thumbnail.']);
    }

    public function test_wrong_file_type_has_an_indonesian_message(): void
    {
        $this->actingAs($this->makeInstructor())->withSession(['active_role' => 'instructor'])
            ->post(route('instructor.courses.store'), $this->payload([
                'thumbnail' => UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'),
            ]))
            ->assertSessionHasErrors(['thumbnail' => 'Thumbnail harus berupa gambar (JPG, PNG, atau GIF).']);
    }

    public function test_too_big_thumbnail_on_update_shows_the_error_box_on_the_edit_page(): void
    {
        Storage::fake('public');
        $owner = $this->makeInstructor();
        $course = $this->makeCourse($owner);
        $editUrl = route('instructor.courses.edit', $course);

        $this->actingAs($owner)->withSession(['active_role' => 'instructor'])->from($editUrl)
            ->put(route('instructor.courses.update', $course), $this->payload([
                'category_id' => $course->category_id,
                'thumbnail' => UploadedFile::fake()->image('besar.jpg', 100, 100)->size(3000),
            ]))
            ->assertRedirect($editUrl)
            ->assertSessionHasErrors(['thumbnail' => 'Ukuran thumbnail maksimal 2 MB.']);

        $this->actingAs($owner)->withSession(['active_role' => 'instructor'])->get($editUrl)
            ->assertOk()
            ->assertSee('Kursus belum tersimpan')
            ->assertSee('Pilih ulang gambar thumbnail');
    }

    public function test_course_forms_load_the_shared_shrink_script_once_and_mark_the_input(): void
    {
        $owner = $this->makeInstructor();
        $course = $this->makeCourse($owner);

        foreach ([route('instructor.courses.create'), route('instructor.courses.edit', $course)] as $url) {
            $html = $this->actingAs($owner)->withSession(['active_role' => 'instructor'])->get($url)
                ->assertOk()
                ->assertSee('data-rs-shrink', false)
                ->assertSee('data-max-bytes="2097152"', false)
                ->assertSee('data-accept="image/jpeg,image/png,image/gif"', false)
                ->getContent();

            $this->assertSame(1, substr_count($html, '<script src="'.asset('js/rs-image-shrink.js').'">'), $url);
        }
    }

    public function test_course_form_errors_are_in_indonesian_for_every_field(): void
    {
        Storage::fake('public');
        $owner = $this->makeInstructor();
        $createUrl = route('instructor.courses.create');

        $this->actingAs($owner)->withSession(['active_role' => 'instructor'])->from($createUrl)
            ->post(route('instructor.courses.store'), $this->payload([
                'title' => '',
                'availability_type' => 'period',
                'thumbnail' => UploadedFile::fake()->image('kecil.jpg', 100, 100),
            ]))
            ->assertRedirect($createUrl)
            ->assertSessionHasErrors([
                'title' => 'Judul kursus wajib diisi.',
                'start_date' => 'Tanggal mulai wajib diisi untuk ketersediaan berjangka.',
                'end_date' => 'Tanggal selesai wajib diisi untuk ketersediaan berjangka.',
            ]);

        $html = $this->actingAs($owner)->withSession(['active_role' => 'instructor'])->get($createUrl)
            ->assertOk()
            ->assertSee('Judul kursus wajib diisi.')
            ->assertSee('Tanggal mulai wajib diisi untuk ketersediaan berjangka.')
            ->getContent();

        $this->assertDoesNotMatchRegularExpression('/The [a-z_ .]+ (field|must)/', $html);
    }

    public function test_invalid_choices_and_dates_are_in_indonesian(): void
    {
        $this->actingAs($this->makeInstructor())->withSession(['active_role' => 'instructor'])
            ->post(route('instructor.courses.store'), $this->payload([
                'category_id' => 99999,
                'payment_type' => 'barter',
                'availability_type' => 'period',
                'start_date' => '2026-12-31',
                'end_date' => '2026-01-01',
                'thumbnail' => UploadedFile::fake()->image('kecil.jpg', 100, 100),
            ]))
            ->assertSessionHasErrors([
                'category_id' => 'Kategori yang dipilih tidak valid.',
                'payment_type' => 'Jenis pembayaran yang dipilih tidak valid.',
                'end_date' => 'Tanggal selesai harus sama dengan atau setelah tanggal mulai.',
            ]);
    }

    public function test_question_create_page_still_loads_the_shrink_script_once(): void
    {
        $owner = $this->makeInstructor();
        $topic = \App\Models\QuestionTopic::create([
            'instructor_id' => $owner->id,
            'name' => 'Topik',
            'available_for_all_courses' => true,
        ]);

        $html = $this->actingAs($owner)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.question-bank.questions.create', [$topic, 'type' => 'multiple_choice_single']))
            ->assertOk()->getContent();

        $tag = '<script src="'.asset('js/rs-image-shrink.js').'">';
        $this->assertSame(1, substr_count($html, $tag));
        $this->assertGreaterThan(strpos($html, 'vendor/jodit/jodit.min.js'), strpos($html, $tag));
    }
}

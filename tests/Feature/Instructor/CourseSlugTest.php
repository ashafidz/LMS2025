<?php

namespace Tests\Feature\Instructor;

use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class CourseSlugTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Judul Sama',
            'category_id' => CourseCategory::factory()->create()->id,
            'description' => '<p>Deskripsi</p>',
            'availability_type' => 'lifetime',
            'payment_type' => 'money',
        ], $overrides);
    }

    private function as($user)
    {
        return $this->actingAs($user)->withSession(['active_role' => 'instructor']);
    }

    public function test_two_instructors_can_store_courses_with_the_same_title(): void
    {
        Storage::fake('public');

        foreach ([$this->makeInstructor(), $this->makeInstructor()] as $instructor) {
            $this->as($instructor)->post(route('instructor.courses.store'), $this->payload([
                'thumbnail' => UploadedFile::fake()->image('a.jpg', 100, 100),
            ]))->assertRedirect(route('instructor.courses.index'))->assertSessionHasNoErrors();
        }

        $this->assertSame(['judul-sama', 'judul-sama-1'], Course::orderBy('id')->pluck('slug')->all());
    }

    public function test_renaming_to_a_title_used_by_another_course_gets_a_suffix(): void
    {
        $owner = $this->makeInstructor();
        $a = $this->makeCourse($owner, ['title' => 'Judul Sama', 'slug' => 'judul-sama']);
        $b = $this->makeCourse($owner, ['title' => 'Judul Lain']);

        $this->as($owner)->put(route('instructor.courses.update', $b), $this->payload([
            'category_id' => $b->category_id,
        ]))->assertRedirect(route('instructor.courses.index'))->assertSessionHasNoErrors();

        $this->assertSame('judul-sama', $a->fresh()->slug);
        $this->assertSame('judul-sama-1', $b->fresh()->slug);
    }

    public function test_updating_without_changing_the_title_keeps_the_slug(): void
    {
        $owner = $this->makeInstructor();
        $course = $this->makeCourse($owner, ['title' => 'Judul Tetap']);
        $course->forceFill(['slug' => 'alamat-lama'])->save();

        $this->as($owner)->put(route('instructor.courses.update', $course), $this->payload([
            'title' => 'Judul Tetap',
            'category_id' => $course->category_id,
            'description' => '<p>Deskripsi baru</p>',
        ]))->assertSessionHasNoErrors();

        $this->assertSame('alamat-lama', $course->fresh()->slug);
    }

    public function test_a_soft_deleted_course_still_reserves_its_slug(): void
    {
        $owner = $this->makeInstructor();
        $old = $this->makeCourse($owner, ['title' => 'Lama']);
        $old->forceFill(['slug' => 'x'])->save();
        $old->delete();

        $this->assertSame('x-1', Course::uniqueSlug('X'));
        $this->assertSame('kursus', Course::uniqueSlug('!!!'));
    }

    public function test_clone_still_gets_a_unique_slug(): void
    {
        $owner = $this->makeInstructor();
        $course = $this->makeCourse($owner, ['title' => 'Asli']);

        $this->as($owner)->post(route('instructor.courses.clone', $course))->assertSessionHasNoErrors();
        $this->as($owner)->post(route('instructor.courses.clone', $course))->assertSessionHasNoErrors();

        $this->assertSame(
            ['asli-salinan', 'asli-salinan-1'],
            Course::where('title', 'Asli (Salinan)')->orderBy('id')->pluck('slug')->all()
        );
    }

    public function test_course_list_shows_indonesian_status_names(): void
    {
        $owner = $this->makeInstructor();
        $this->makeCourse($owner, ['title' => 'Satu', 'status' => 'published']);
        $this->makeCourse($owner, ['title' => 'Dua', 'status' => 'pending_review']);

        $this->as($owner)->get(route('instructor.courses.index'))
            ->assertOk()
            ->assertSee('Terbit')
            ->assertSee('Menunggu tinjauan')
            ->assertDontSee('Published')
            ->assertDontSee('Pending review');
    }
}

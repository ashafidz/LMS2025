<?php

namespace Tests\Feature\Instructor;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class CourseDescriptionEditorTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private function as($user)
    {
        return $this->actingAs($user)->withSession(['active_role' => 'instructor']);
    }

    public function test_create_page_loads_jodit_and_keeps_the_description_field(): void
    {
        $this->as($this->makeInstructor())->get(route('instructor.courses.create'))
            ->assertOk()
            ->assertSee('vendor/jodit/jodit.min.js', false)
            ->assertSee('name="description"', false);
    }

    public function test_edit_page_loads_jodit_and_shows_the_saved_description(): void
    {
        $instructor = $this->makeInstructor();
        $course = $this->makeCourse($instructor, ['description' => '<p>Isi lama</p>']);

        $this->as($instructor)->get(route('instructor.courses.edit', $course))
            ->assertOk()
            ->assertSee('vendor/jodit/jodit.min.js', false)
            ->assertSee('name="description"', false)
            ->assertSee('Isi lama');
    }

    public function test_validation_messages_are_indonesian_with_indonesian_field_names(): void
    {
        $instructor = $this->makeInstructor();
        $course = $this->makeCourse($instructor);

        $this->as($instructor)->post(route('instructor.courses.modules.store', $course), ['title' => ''])
            ->assertSessionHasErrors(['title' => 'Judul wajib diisi.']);
    }

    public function test_module_create_page_shows_server_errors_in_indonesian(): void
    {
        $instructor = $this->makeInstructor();
        $course = $this->makeCourse($instructor);

        $this->as($instructor)->from(route('instructor.courses.modules.create', $course))
            ->followingRedirects()
            ->post(route('instructor.courses.modules.store', $course), ['title' => ''])
            ->assertSee('Modul belum tersimpan.')
            ->assertSee('Judul wajib diisi.');
    }
}

<?php

namespace Tests\Concerns;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonArticle;
use App\Models\Module;
use App\Models\User;

trait BuildsLessonFixtures
{
    protected function makeCourse(User $instructor, array $attributes = []): Course
    {
        return Course::factory()->create(array_merge([
            'instructor_id' => $instructor->id,
            'status' => 'published',
        ], $attributes));
    }

    protected function makeModule(Course $course, int $pointsRequired = 0): Module
    {
        return Module::factory()->create([
            'course_id' => $course->id,
            'points_required' => $pointsRequired,
        ]);
    }

    protected function makeArticleLesson(Module $module): Lesson
    {
        $article = LessonArticle::create(['content' => '<p>Isi artikel</p>']);

        return Lesson::factory()->create([
            'module_id' => $module->id,
            'lessonable_id' => $article->id,
            'lessonable_type' => LessonArticle::class,
        ]);
    }

    protected function enroll(User $student, Course $course): void
    {
        $student->enrollments()->syncWithoutDetaching([$course->id => ['enrolled_at' => now()]]);
    }
}

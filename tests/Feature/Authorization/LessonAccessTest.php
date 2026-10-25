<?php

namespace Tests\Feature\Authorization;

use App\Services\LessonAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class LessonAccessTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    public function test_other_instructor_is_not_a_manager_and_cannot_view_course(): void
    {
        $owner = $this->makeInstructor();
        $other = $this->makeInstructor();
        $course = $this->makeCourse($owner);

        $this->assertTrue(LessonAccess::isManager($owner, $course));
        $this->assertFalse(LessonAccess::isManager($other, $course));
        $this->assertFalse(LessonAccess::canViewCourse($other, $course));
    }

    public function test_admin_is_manager(): void
    {
        $course = $this->makeCourse($this->makeInstructor());

        $this->assertTrue(LessonAccess::isManager($this->makeAdmin(), $course));
        $this->assertTrue(LessonAccess::isManager($this->makeSuperadmin(), $course));
    }

    public function test_enrolled_student_can_view_open_module_lesson(): void
    {
        $course = $this->makeCourse($this->makeInstructor());
        $lesson = $this->makeArticleLesson($this->makeModule($course));
        $student = $this->makeStudent();
        $this->enroll($student, $course);

        $this->assertTrue(LessonAccess::canAccessLesson($student, $lesson));
        $this->assertFalse(LessonAccess::canAccessLesson($this->makeStudent(), $lesson));
    }

    public function test_locked_module_blocks_student_until_points_are_enough_but_not_owner(): void
    {
        $owner = $this->makeInstructor();
        $course = $this->makeCourse($owner);
        $module = $this->makeModule($course, 100);
        $lesson = $this->makeArticleLesson($module);
        $student = $this->makeStudent();
        $this->enroll($student, $course);

        $this->assertFalse(LessonAccess::canAccessLesson($student, $lesson));
        $this->assertTrue(LessonAccess::canAccessLesson($owner, $lesson));

        $student->coursePoints()->attach($course->id, ['points_earned' => 100]);

        $this->assertTrue(LessonAccess::canAccessLesson($student->fresh(), $lesson));
    }

    public function test_guest_has_no_access(): void
    {
        $course = $this->makeCourse($this->makeInstructor());
        $lesson = $this->makeArticleLesson($this->makeModule($course));

        $this->assertFalse(LessonAccess::canAccessLesson(null, $lesson));
    }
}

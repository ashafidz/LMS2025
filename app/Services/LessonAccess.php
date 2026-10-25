<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;

/**
 * Single place that decides who may open a course, a module and a lesson.
 *
 * - Manager: the course owner or an admin/superadmin. May preview and ignores module locks.
 * - Enrolled student: may open the course; modules locked by points stay closed until the points are reached.
 * Other instructors are NOT managers of someone else's course.
 */
class LessonAccess
{
    public static function isManager(?User $user, Course $course): bool
    {
        return $user !== null
            && ((int) $course->instructor_id === (int) $user->id || $user->hasAnyRole(['admin', 'superadmin']));
    }

    public static function isEnrolled(?User $user, Course $course): bool
    {
        return $user !== null && $user->enrollments()->where('courses.id', $course->id)->exists();
    }

    public static function canViewCourse(?User $user, Course $course): bool
    {
        return self::isManager($user, $course) || self::isEnrolled($user, $course);
    }

    public static function isModuleUnlocked(?User $user, Module $module): bool
    {
        if ($user === null) {
            return false;
        }

        if ((int) $module->points_required <= 0 || self::isManager($user, $module->course)) {
            return true;
        }

        $pivot = $user->coursePoints()->where('course_id', $module->course_id)->first();
        $points = (int) ($pivot->pivot->points_earned ?? 0);

        return $points >= (int) $module->points_required;
    }

    public static function canAccessLesson(?User $user, Lesson $lesson): bool
    {
        $module = $lesson->module;

        return self::canViewCourse($user, $module->course) && self::isModuleUnlocked($user, $module);
    }
}

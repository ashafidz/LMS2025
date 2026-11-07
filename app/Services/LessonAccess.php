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
    /**
     * Home and list crumbs for preview pages, following the active role (admins cannot open instructor pages).
     *
     * @return array{0: array{0: string, 1: string}, 1: array{0: string, 1: string}} [[home label, url], [list label, url]]
     */
    public static function previewCrumbs(?string $activeRole): array
    {
        return match ($activeRole) {
            'superadmin' => [['__home', route('superadmin.dashboard')], ['Publikasi Kursus', route('superadmin.publication.index')]],
            'admin' => [['__home', route('admin.dashboard')], ['Publikasi Kursus', route('admin.publication.index')]],
            default => [['__home', route('instructor.dashboard')], ['Kursus Saya', route('instructor.courses.index')]],
        };
    }

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

        return self::meetsPointsRequirement((int) ($pivot->pivot->points_earned ?? 0), $module);
    }

    /**
     * The lock rule itself: a module with points_required > 0 stays closed until the student has that many course points.
     * Used by isModuleUnlocked() and by pages that already hold the points (for example the student home).
     */
    public static function meetsPointsRequirement(int $pointsEarned, Module $module): bool
    {
        return (int) $module->points_required <= 0 || $pointsEarned >= (int) $module->points_required;
    }

    public static function canAccessLesson(?User $user, Lesson $lesson): bool
    {
        $module = $lesson->module;

        return self::canViewCourse($user, $module->course) && self::isModuleUnlocked($user, $module);
    }
}

<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Determine whether the user can manage the course.
     */
    public function manage(User $user, Course $course): bool
    {
        return $course->instructor_id === $user->id;
    }
}

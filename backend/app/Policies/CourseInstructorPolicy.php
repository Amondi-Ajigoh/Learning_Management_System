<?php

namespace App\Policies;

use App\Models\CourseInstructor;
use App\Models\User;

class CourseInstructorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view courses');
    }

    public function view(User $user, CourseInstructor $courseInstructor): bool
    {
        return $user->can('view courses');
    }

    public function create(User $user): bool
    {
        return $user->can('manage instructors');
    }

    public function update(User $user, CourseInstructor $courseInstructor): bool
    {
        return $user->can('manage instructors');
    }

    public function delete(User $user, CourseInstructor $courseInstructor): bool
    {
        return $user->can('manage instructors');
    }
}

<?php

namespace App\Policies;

use App\Models\CourseCategory;
use App\Models\User;

class CourseCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view courses');
    }

    public function view(User $user, CourseCategory $courseCategory): bool
    {
        return $user->can('view courses');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, CourseCategory $courseCategory): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, CourseCategory $courseCategory): bool
    {
        return $user->hasRole('admin');
    }
}

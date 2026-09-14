<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view courses');
    }

    public function view(User $user, Course $course): bool
    {
        if ($course->status?->value === 'published') {
            return $user->can('view courses');
        }

        return $user->hasRole('admin')
            || ($user->can('view courses') && $this->managesCourse($user, $course));
    }

    public function create(User $user): bool
    {
        return $user->can('create courses');
    }

    public function update(User $user, Course $course): bool
    {
        return $user->can('update courses')
            && $this->managesCourse($user, $course);
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->can('delete courses')
            && $this->managesCourse($user, $course);
    }

    public function publish(User $user, Course $course): bool
    {
        return $user->can('publish courses')
            && $this->managesCourse($user, $course);
    }

    private function managesCourse(User $user, Course $course): bool
    {
        return $user->hasRole('admin')
            || $course->created_by === $user->id
            || $course->instructors()->whereKey($user->id)->exists();
    }
}

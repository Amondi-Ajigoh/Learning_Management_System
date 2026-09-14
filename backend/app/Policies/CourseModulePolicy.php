<?php

namespace App\Policies;

use App\Models\CourseModule;
use App\Models\User;

class CourseModulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view course content');
    }

    public function view(User $user, CourseModule $courseModule): bool
    {
        if ($courseModule->is_published) {
            return $user->can('view course content');
        }

        return $this->managesCourse($user, $courseModule);
    }

    public function create(User $user, CourseModule $courseModule): bool
    {
        return $user->can('create course content')
            && $this->managesCourse($user, $courseModule);
    }

    public function update(User $user, CourseModule $courseModule): bool
    {
        return $user->can('update course content')
            && $this->managesCourse($user, $courseModule);
    }

    public function delete(User $user, CourseModule $courseModule): bool
    {
        return $user->can('delete course content')
            && $this->managesCourse($user, $courseModule);
    }

    private function managesCourse(User $user, CourseModule $courseModule): bool
    {
        $course = $courseModule->course;

        return $user->hasRole('admin')
            || $course->created_by === $user->id
            || $course->instructors()->whereKey($user->id)->exists();
    }
}

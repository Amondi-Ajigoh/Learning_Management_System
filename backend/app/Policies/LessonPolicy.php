<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view course content');
    }

    public function view(User $user, Lesson $lesson): bool
    {
        if ($lesson->is_published && $lesson->module->is_published) {
            return $user->can('view course content');
        }

        return $this->managesCourse($user, $lesson);
    }

    public function create(User $user, Lesson $lesson): bool
    {
        return $user->can('create course content')
            && $this->managesCourse($user, $lesson);
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->can('update course content')
            && $this->managesCourse($user, $lesson);
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $user->can('delete course content')
            && $this->managesCourse($user, $lesson);
    }

    private function managesCourse(User $user, Lesson $lesson): bool
    {
        $course = $lesson->module->course;

        return $user->hasRole('admin')
            || $course->created_by === $user->id
            || $course->instructors()->whereKey($user->id)->exists();
    }
}

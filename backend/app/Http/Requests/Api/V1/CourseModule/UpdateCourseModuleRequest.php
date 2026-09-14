<?php

namespace App\Http\Requests\Api\V1\CourseModule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $module = $this->route('courseModule');
        $course = $module?->course;

        return $course
            && $this->user()?->can('update course content')
            && (
                $this->user()->hasRole('admin')
                || $course->created_by === $this->user()->id
                || $course->instructors()->whereKey($this->user()->id)->exists()
            );
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'position' => ['sometimes', 'required', 'integer', 'min:1'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests\Api\V1\CourseModule;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course
            && $this->user()?->can('create course content')
            && (
                $this->user()->hasRole('admin')
                || $course->created_by === $this->user()->id
                || $course->instructors()->whereKey($this->user()->id)->exists()
            );
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'position' => ['required', 'integer', 'min:1'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }
}

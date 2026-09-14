<?php

namespace App\Http\Requests\Api\V1\Lesson;

use App\Enums\LessonType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $module = $this->route('courseModule');
        $course = $module?->course;

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
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('lessons', 'slug')
                    ->where(fn ($query) => $query->where('module_id', $this->route('courseModule')->id)),
            ],
            'summary' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'type' => ['required', Rule::enum(LessonType::class)],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'position' => ['required', 'integer', 'min:1'],
            'is_preview' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests\Api\V1\Lesson;

use App\Enums\LessonType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lesson = $this->route('lesson');
        $course = $lesson?->module?->course;

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
        $lesson = $this->route('lesson');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('lessons', 'slug')
                    ->where(fn ($query) => $query->where('module_id', $lesson->module_id))
                    ->ignore($lesson->id),
            ],
            'summary' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'type' => ['sometimes', 'required', Rule::enum(LessonType::class)],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'position' => ['sometimes', 'required', 'integer', 'min:1'],
            'is_preview' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }
}

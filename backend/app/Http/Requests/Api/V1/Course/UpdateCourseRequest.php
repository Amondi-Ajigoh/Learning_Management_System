<?php

namespace App\Http\Requests\Api\V1\Course;

use App\Enums\CourseLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course
            && ($this->user()?->can('update', $course) ?? false);
    }

    public function rules(): array
    {
        $course = $this->route('course');

        return [
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:course_categories,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('courses', 'slug')->ignore($course),
            ],
            'short_description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'description' => ['sometimes', 'nullable', 'string'],
            'thumbnail_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'level' => ['sometimes', Rule::enum(CourseLevel::class)],
            'language' => ['sometimes', 'required', 'string', 'size:2'],
            'estimated_minutes' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100000'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0', 'max:99999999.99'],
            'currency' => ['sometimes', 'required', 'string', 'size:3', 'uppercase'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->filled('title') && ! $this->filled('slug')) {
            $data['slug'] = Str::slug($this->string('title')->toString());
        }

        if ($this->filled('language')) {
            $data['language'] = strtolower($this->string('language')->toString());
        }

        if ($this->filled('currency')) {
            $data['currency'] = strtoupper($this->string('currency')->toString());
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }
}

<?php

namespace App\Http\Requests\Api\V1\Course;

use App\Enums\CourseLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create courses') ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', 'exists:course_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', 'unique:courses,slug'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'thumbnail_url' => ['nullable', 'url', 'max:2048'],
            'level' => ['required', Rule::enum(CourseLevel::class)],
            'language' => ['required', 'string', 'size:2'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'currency' => ['required', 'string', 'size:3', 'uppercase'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if (! $this->filled('slug') && $this->filled('title')) {
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

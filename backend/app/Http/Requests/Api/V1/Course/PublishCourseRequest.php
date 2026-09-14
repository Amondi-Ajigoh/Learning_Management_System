<?php

namespace App\Http\Requests\Api\V1\Course;

use Illuminate\Foundation\Http\FormRequest;

class PublishCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course
            && ($this->user()?->can('publish courses') ?? false)
            && ($this->user()?->can('update', $course) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}

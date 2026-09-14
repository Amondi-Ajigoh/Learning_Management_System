<?php

namespace App\Http\Requests\Api\V1\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignInstructorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course
            && ($this->user()?->can('manage instructors') ?? false)
            && ($this->user()?->can('update', $course) ?? false);
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->where('is_active', true)
                ),
            ],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}

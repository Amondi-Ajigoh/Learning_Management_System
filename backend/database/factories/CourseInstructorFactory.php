<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseInstructor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseInstructor>
 */
class CourseInstructorFactory extends Factory
{
    protected $model = CourseInstructor::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'user_id' => User::factory(),
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn () => [
            'is_primary' => true,
        ]);
    }
}

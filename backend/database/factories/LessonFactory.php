<?php

namespace Database\Factories;

use App\Enums\LessonType;
use App\Models\CourseModule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LessonFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'module_id' => CourseModule::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 999999),
            'summary' => fake()->optional()->sentence(),
            'content' => fake()->optional()->paragraphs(2, true),
            'type' => fake()->randomElement(LessonType::cases()),
            'duration_minutes' => fake()->numberBetween(5, 120),
            'position' => fake()->unique()->numberBetween(1, 100),
            'is_preview' => false,
            'is_published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'is_published' => true,
        ]);
    }

    public function preview(): static
    {
        return $this->state(fn () => [
            'is_preview' => true,
        ]);
    }
}

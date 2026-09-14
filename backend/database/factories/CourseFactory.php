<?php

namespace Database\Factories;

use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'category_id' => CourseCategory::factory(),
            'created_by' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'thumbnail_url' => fake()->imageUrl(1280, 720, 'education'),
            'level' => fake()->randomElement(CourseLevel::cases()),
            'language' => 'en',
            'estimated_minutes' => fake()->numberBetween(30, 1200),
            'price' => fake()->randomFloat(2, 0, 500),
            'currency' => 'USD',
            'status' => CourseStatus::Draft,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => CourseStatus::Published,
            'published_at' => now(),
        ]);
    }

    public function review(): static
    {
        return $this->state(fn () => [
            'status' => CourseStatus::Review,
            'published_at' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => CourseStatus::Archived,
            'published_at' => null,
        ]);
    }
}

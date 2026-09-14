<?php

namespace Tests\Feature\Api\V1;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourseCatalogApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    use RefreshDatabase;

    public function test_public_catalog_returns_only_published_courses(): void
    {
        Course::factory()->published()->create(['title' => 'Published Laravel Course']);
        Course::factory()->create(['title' => 'Draft Laravel Course']);

        $response = $this->getJson('/api/v1/courses');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Published Laravel Course');
    }

    public function test_public_catalog_can_search_and_filter_courses(): void
    {
        $category = CourseCategory::factory()->create([
            'name' => 'Web Development',
            'slug' => 'web-development',
        ]);

        Course::factory()->published()->create([
            'category_id' => $category->id,
            'title' => 'Advanced Laravel APIs',
            'level' => 'advanced',
        ]);

        Course::factory()->published()->create([
            'title' => 'Introduction to Biology',
            'level' => 'beginner',
        ]);

        $response = $this->getJson(
            '/api/v1/courses?search=Laravel&category=web-development&level=advanced'
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Advanced Laravel APIs');
    }

    public function test_public_course_detail_is_available_for_published_course(): void
    {
        $course = Course::factory()->published()->create();

        $response = $this->getJson("/api/v1/courses/{$course->id}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $course->id)
            ->assertJsonPath('data.status', CourseStatus::Published->value);
    }

    public function test_public_user_cannot_view_draft_course(): void
    {
        $course = Course::factory()->create();

        $this->getJson("/api/v1/courses/{$course->id}")
            ->assertNotFound();
    }

    public function test_instructor_can_create_course(): void
    {
        $instructor = User::factory()->create();
        $instructor->assignRole('instructor');

        Sanctum::actingAs($instructor);

        $response = $this->postJson('/api/v1/courses', [
            'title' => 'Building Production Laravel APIs',
            'level' => 'advanced',
            'language' => 'en',
            'price' => 99.99,
            'currency' => 'USD',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Building Production Laravel APIs')
            ->assertJsonPath('data.status', 'draft');

        $this->assertDatabaseHas('courses', [
            'created_by' => $instructor->id,
            'title' => 'Building Production Laravel APIs',
            'status' => 'draft',
        ]);
    }

    public function test_student_cannot_create_course(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        Sanctum::actingAs($student);

        $this->postJson('/api/v1/courses', [
            'title' => 'Unauthorized Course',
            'level' => 'beginner',
            'language' => 'en',
            'price' => 0,
            'currency' => 'USD',
        ])->assertForbidden();
    }

    public function test_course_can_be_submitted_for_review_and_published(): void
    {
        $instructor = User::factory()->create();
        $instructor->assignRole('instructor');

        Sanctum::actingAs($instructor);

        $course = Course::factory()->create([
            'created_by' => $instructor->id,
            'status' => CourseStatus::Draft,
        ]);

        $this->postJson("/api/v1/courses/{$course->id}/submit-for-review")
            ->assertOk()
            ->assertJsonPath('data.status', 'review');

        $this->postJson("/api/v1/courses/{$course->id}/publish")
            ->assertOk()
            ->assertJsonPath('data.status', 'published');

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'status' => 'published',
        ]);
    }

    public function test_admin_can_manage_categories(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Software Engineering',
            'description' => 'Professional software engineering courses.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Software Engineering')
            ->assertJsonPath('data.slug', 'software-engineering');

        $category = CourseCategory::query()
            ->where('slug', 'software-engineering')
            ->firstOrFail();

        $this->patchJson("/api/v1/categories/{$category->id}", [
            'name' => 'Software Engineering & Development',
        ])
            ->assertOk()
            ->assertJsonPath('data.slug', 'software-engineering-development');
    }

    public function test_admin_can_assign_an_instructor_to_a_course(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $instructor = User::factory()->create();
        $instructor->assignRole('instructor');

        $course = Course::factory()->create([
            'created_by' => $admin->id,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson(
            "/api/v1/courses/{$course->id}/instructors",
            [
                'user_id' => $instructor->id,
                'is_primary' => true,
            ]
        );

        $response
            ->assertCreated()
            ->assertJsonPath('data.is_primary', true)
            ->assertJsonPath('data.instructor.id', $instructor->id);

        $this->assertDatabaseHas('course_instructors', [
            'course_id' => $course->id,
            'user_id' => $instructor->id,
            'is_primary' => true,
        ]);
    }
}

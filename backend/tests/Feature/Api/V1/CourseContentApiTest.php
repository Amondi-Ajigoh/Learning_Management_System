<?php

namespace Tests\Feature\Api\V1;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourseContentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_public_can_view_published_course_modules_and_lessons(): void
    {
        $course = Course::factory()->create([
            'status' => 'published',
        ]);

        $publishedModule = CourseModule::factory()
            ->published()
            ->create([
                'course_id' => $course->id,
                'position' => 1,
            ]);

        $draftModule = CourseModule::factory()->create([
            'course_id' => $course->id,
            'position' => 2,
        ]);

        $publishedLesson = Lesson::factory()
            ->published()
            ->create([
                'module_id' => $publishedModule->id,
                'position' => 1,
            ]);

        Lesson::factory()->create([
            'module_id' => $publishedModule->id,
            'position' => 2,
        ]);

        $this->getJson("/api/v1/courses/{$course->id}/modules")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $publishedModule->id);

        $this->getJson("/api/v1/course-modules/{$publishedModule->id}/lessons")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $publishedLesson->id);

        $this->getJson("/api/v1/course-modules/{$draftModule->id}")
            ->assertNotFound();

        $this->getJson("/api/v1/lessons/{$publishedLesson->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $publishedLesson->id);
    }

    public function test_public_cannot_view_published_content_from_a_draft_course(): void
    {
        $course = Course::factory()->create([
            'status' => 'draft',
        ]);

        $module = CourseModule::factory()
            ->published()
            ->create([
                'course_id' => $course->id,
                'position' => 1,
            ]);

        $lesson = Lesson::factory()
            ->published()
            ->create([
                'module_id' => $module->id,
                'position' => 1,
            ]);

        $this->getJson("/api/v1/courses/{$course->id}/modules")
            ->assertNotFound();

        $this->getJson("/api/v1/course-modules/{$module->id}")
            ->assertNotFound();

        $this->getJson("/api/v1/course-modules/{$module->id}/lessons")
            ->assertNotFound();

        $this->getJson("/api/v1/lessons/{$lesson->id}")
            ->assertNotFound();
    }

    public function test_instructor_can_create_module_for_owned_course(): void
    {
        $instructor = User::factory()->create();
        $instructor->assignRole('instructor');

        $course = Course::factory()->create([
            'created_by' => $instructor->id,
        ]);

        Sanctum::actingAs($instructor);

        $response = $this->postJson(
            "/api/v1/courses/{$course->id}/modules",
            [
                'title' => 'Getting Started',
                'description' => 'Introduction to the course.',
                'position' => 1,
                'is_published' => false,
            ]
        );

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Getting Started')
            ->assertJsonPath('data.position', 1)
            ->assertJsonPath('data.is_published', false);

        $this->assertDatabaseHas('course_modules', [
            'course_id' => $course->id,
            'title' => 'Getting Started',
            'position' => 1,
        ]);
    }

    public function test_student_cannot_create_module(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $course = Course::factory()->create();

        Sanctum::actingAs($student);

        $this->postJson(
            "/api/v1/courses/{$course->id}/modules",
            [
                'title' => 'Unauthorized Module',
                'position' => 1,
            ]
        )->assertForbidden();
    }

    public function test_instructor_can_create_lesson_and_slug_is_generated(): void
    {
        $instructor = User::factory()->create();
        $instructor->assignRole('instructor');

        $course = Course::factory()->create([
            'created_by' => $instructor->id,
        ]);

        $module = CourseModule::factory()->create([
            'course_id' => $course->id,
        ]);

        Sanctum::actingAs($instructor);

        $response = $this->postJson(
            "/api/v1/course-modules/{$module->id}/lessons",
            [
                'title' => 'Introduction to Laravel APIs',
                'summary' => 'Learn the fundamentals.',
                'content' => 'Lesson content.',
                'type' => 'text',
                'duration_minutes' => 30,
                'position' => 1,
                'is_preview' => true,
                'is_published' => false,
            ]
        );

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Introduction to Laravel APIs')
            ->assertJsonPath('data.slug', 'introduction-to-laravel-apis')
            ->assertJsonPath('data.is_preview', true);

        $this->assertDatabaseHas('lessons', [
            'module_id' => $module->id,
            'slug' => 'introduction-to-laravel-apis',
        ]);
    }

    public function test_student_cannot_modify_lesson(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $course = Course::factory()->create();
        $module = CourseModule::factory()->create([
            'course_id' => $course->id,
        ]);
        $lesson = Lesson::factory()->create([
            'module_id' => $module->id,
        ]);

        Sanctum::actingAs($student);

        $this->patchJson("/api/v1/lessons/{$lesson->id}", [
            'title' => 'Unauthorized Update',
        ])->assertForbidden();

        $this->deleteJson("/api/v1/lessons/{$lesson->id}")
            ->assertForbidden();
    }

    public function test_instructor_can_update_and_delete_course_content(): void
    {
        $instructor = User::factory()->create();
        $instructor->assignRole('instructor');

        $course = Course::factory()->create([
            'created_by' => $instructor->id,
        ]);

        $module = CourseModule::factory()->create([
            'course_id' => $course->id,
        ]);

        $lesson = Lesson::factory()->create([
            'module_id' => $module->id,
        ]);

        Sanctum::actingAs($instructor);

        $this->patchJson("/api/v1/course-modules/{$module->id}", [
            'title' => 'Updated Module',
            'position' => 2,
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated Module')
            ->assertJsonPath('data.position', 2);

        $this->patchJson("/api/v1/lessons/{$lesson->id}", [
            'title' => 'Updated Lesson',
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated Lesson')
            ->assertJsonPath('data.slug', 'updated-lesson');

        $this->deleteJson("/api/v1/lessons/{$lesson->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Lesson deleted successfully.');

        $this->assertDatabaseMissing('lessons', [
            'id' => $lesson->id,
        ]);
    }

    public function test_module_deletion_cascades_to_lessons(): void
    {
        $instructor = User::factory()->create();
        $instructor->assignRole('instructor');

        $course = Course::factory()->create([
            'created_by' => $instructor->id,
        ]);

        $module = CourseModule::factory()->create([
            'course_id' => $course->id,
        ]);

        $lesson = Lesson::factory()->create([
            'module_id' => $module->id,
        ]);

        Sanctum::actingAs($instructor);

        $this->deleteJson("/api/v1/course-modules/{$module->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Course module deleted successfully.');

        $this->assertDatabaseMissing('course_modules', [
            'id' => $module->id,
        ]);

        $this->assertDatabaseMissing('lessons', [
            'id' => $lesson->id,
        ]);
    }
}

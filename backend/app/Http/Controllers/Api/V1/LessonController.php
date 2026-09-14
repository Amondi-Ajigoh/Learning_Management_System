<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Lesson\StoreLessonRequest;
use App\Http\Requests\Api\V1\Lesson\UpdateLessonRequest;
use App\Http\Resources\Api\V1\LessonResource;
use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class LessonController extends Controller
{
    public function index(
        Request $request,
        CourseModule $courseModule
    ): AnonymousResourceCollection {
        $course = $courseModule->course;
        $canManage = $request->user()
            && $this->canManageCourse($request->user(), $course);

        if (
            ! $canManage
            && (
                $course->status?->value !== 'published'
                || ! $courseModule->is_published
            )
        ) {
            abort(404);
        }

        $query = $courseModule->lessons()
            ->withCount('materials');

        if (! $canManage) {
            $query->where('is_published', true);
        }

        return LessonResource::collection($query->get());
    }

    public function store(
        StoreLessonRequest $request,
        CourseModule $courseModule
    ): LessonResource {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($courseModule, $data['title']);
        }

        $lesson = $courseModule->lessons()->create($data);

        return new LessonResource(
            $lesson->loadCount('materials')
        );
    }

    public function show(Request $request, Lesson $lesson): LessonResource
    {
        $course = $lesson->module->course;
        $canManage = $request->user()
            && $this->canManageCourse($request->user(), $course);

        if (
            ! $canManage
            && (
                $course->status?->value !== 'published'
                || ! $lesson->module->is_published
                || ! $lesson->is_published
            )
        ) {
            abort(404);
        }

        $lesson->load([
            'module',
            'materials',
        ])->loadCount('materials');

        return new LessonResource($lesson);
    }

    public function update(
        UpdateLessonRequest $request,
        Lesson $lesson
    ): LessonResource {
        $data = $request->validated();

        if (array_key_exists('title', $data) && ! array_key_exists('slug', $data)) {
            $data['slug'] = $this->uniqueSlug(
                $lesson->module,
                $data['title'],
                $lesson->id
            );
        }

        $lesson->update($data);

        return new LessonResource(
            $lesson->refresh()->loadCount('materials')
        );
    }

    public function destroy(Lesson $lesson): JsonResponse
    {
        $this->authorize('delete', $lesson);

        $lesson->delete();

        return response()->json([
            'message' => 'Lesson deleted successfully.',
        ]);
    }

    private function uniqueSlug(
        CourseModule $module,
        string $title,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 2;

        while (
            $module->lessons()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function canManageCourse($user, $course): bool
    {
        return $user->hasRole('admin')
            || $course->created_by === $user->id
            || $course->instructors()->whereKey($user->id)->exists();
    }
}

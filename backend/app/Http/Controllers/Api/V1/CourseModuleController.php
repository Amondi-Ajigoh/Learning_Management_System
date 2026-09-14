<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CourseModule\StoreCourseModuleRequest;
use App\Http\Requests\Api\V1\CourseModule\UpdateCourseModuleRequest;
use App\Http\Resources\Api\V1\CourseModuleResource;
use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourseModuleController extends Controller
{
    public function index(Request $request, Course $course): AnonymousResourceCollection
    {
        $canManage = $request->user()
            && $this->canManageCourse($request->user(), $course);

        if (! $canManage && $course->status?->value !== 'published') {
            abort(404);
        }

        $query = $course->modules()
            ->withCount('lessons');

        if (! $canManage) {
            $query->where('is_published', true);
        }

        return CourseModuleResource::collection($query->get());
    }

    public function store(
        StoreCourseModuleRequest $request,
        Course $course
    ): CourseModuleResource {
        $module = $course->modules()->create($request->validated());

        return new CourseModuleResource(
            $module->loadCount('lessons')
        );
    }

    public function show(Request $request, CourseModule $courseModule): CourseModuleResource
    {
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

        $courseModule->load([
            'course',
            'lessons' => fn ($query) => $query
                ->when(
                    ! $canManage,
                    fn ($lessons) => $lessons->where('is_published', true)
                ),
        ])->loadCount('lessons');

        return new CourseModuleResource($courseModule);
    }

    public function update(
        UpdateCourseModuleRequest $request,
        CourseModule $courseModule
    ): CourseModuleResource {
        $courseModule->update($request->validated());

        return new CourseModuleResource(
            $courseModule->refresh()->loadCount('lessons')
        );
    }

    public function destroy(CourseModule $courseModule): JsonResponse
    {
        $this->authorize('delete', $courseModule);

        $courseModule->delete();

        return response()->json([
            'message' => 'Course module deleted successfully.',
        ]);
    }

    private function canManageCourse($user, Course $course): bool
    {
        return $user->hasRole('admin')
            || $course->created_by === $user->id
            || $course->instructors()->whereKey($user->id)->exists();
    }
}

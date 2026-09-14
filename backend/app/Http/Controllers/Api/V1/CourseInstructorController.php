<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Course\AssignInstructorRequest;
use App\Http\Resources\Api\V1\CourseInstructorResource;
use App\Models\Course;
use App\Models\CourseInstructor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class CourseInstructorController extends Controller
{
    public function index(Course $course): AnonymousResourceCollection
    {
        $this->authorize('view', $course);

        return CourseInstructorResource::collection(
            $course->courseInstructors()
                ->with('instructor')
                ->orderByDesc('is_primary')
                ->orderBy('id')
                ->get()
        );
    }

    public function store(
        AssignInstructorRequest $request,
        Course $course
    ): CourseInstructorResource {
        $data = $request->validated();

        $instructor = User::query()
            ->whereKey($data['user_id'])
            ->where('is_active', true)
            ->firstOrFail();

        abort_unless($instructor->hasRole('instructor'), 422, 'The selected user is not an instructor.');

        $assignment = DB::transaction(function () use ($course, $data) {
            if (($data['is_primary'] ?? false) === true) {
                $course->courseInstructors()->update([
                    'is_primary' => false,
                ]);
            }

            return $course->courseInstructors()->updateOrCreate(
                ['user_id' => $data['user_id']],
                ['is_primary' => $data['is_primary'] ?? false]
            );
        });

        return new CourseInstructorResource(
            $assignment->load('instructor')
        );
    }

    public function update(
        AssignInstructorRequest $request,
        Course $course,
        CourseInstructor $courseInstructor
    ): CourseInstructorResource {
        abort_unless(
            $courseInstructor->course_id === $course->id,
            404
        );

        $data = $request->validated();

        if (isset($data['user_id'])) {
            $instructor = User::query()
                ->whereKey($data['user_id'])
                ->where('is_active', true)
                ->firstOrFail();

            abort_unless($instructor->hasRole('instructor'), 422, 'The selected user is not an instructor.');
        }

        DB::transaction(function () use ($course, $courseInstructor, $data) {
            if (($data['is_primary'] ?? false) === true) {
                $course->courseInstructors()
                    ->whereKeyNot($courseInstructor->id)
                    ->update(['is_primary' => false]);
            }

            $courseInstructor->update($data);
        });

        return new CourseInstructorResource(
            $courseInstructor->refresh()->load('instructor')
        );
    }

    public function destroy(
        Course $course,
        CourseInstructor $courseInstructor
    ): JsonResponse {
        $this->authorize('update', $course);

        abort_unless(
            $courseInstructor->course_id === $course->id,
            404
        );

        $courseInstructor->delete();

        return response()->json([
            'message' => 'Instructor removed from course successfully.',
        ]);
    }
}

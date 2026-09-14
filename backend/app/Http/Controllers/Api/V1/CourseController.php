<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CourseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Course\PublishCourseRequest;
use App\Http\Requests\Api\V1\Course\StoreCourseRequest;
use App\Http\Requests\Api\V1\Course\UpdateCourseRequest;
use App\Http\Resources\Api\V1\CourseResource;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourseController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Course::query()
            ->with(['category', 'creator', 'courseInstructors.instructor'])
            ->withCount('modules');

        $authenticatedUser = $request->user();

        if (! $authenticatedUser) {
            $query->where('status', CourseStatus::Published->value);
        } else {
            $query->where(function ($builder) use ($authenticatedUser) {
                $builder
                    ->where('status', CourseStatus::Published->value)
                    ->orWhere('created_by', $authenticatedUser->id)
                    ->orWhereHas(
                        'instructors',
                        fn ($instructors) => $instructors->whereKey($authenticatedUser->id)
                    );
            });
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();

            $driver = $query->getModel()->getConnection()->getDriverName();
            $operator = $driver === 'pgsql' ? 'ilike' : 'like';
            $term = "%{$search}%";

            $query->where(function ($builder) use ($operator, $term) {
                $builder
                    ->where('title', $operator, $term)
                    ->orWhere('short_description', $operator, $term)
                    ->orWhere('description', $operator, $term);
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('category')) {
            $category = $request->string('category')->toString();

            $query->whereHas(
                'category',
                fn ($builder) => $builder->where('slug', $category)
            );
        }

        if ($request->filled('level')) {
            $query->where('level', $request->string('level')->toString());
        }

        if ($request->user() && $request->user()->hasRole('admin') && $request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $sort = $request->string('sort', 'latest')->toString();

        match ($sort) {
            'title' => $query->orderBy('title'),
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'oldest' => $query->orderBy('created_at'),
            default => $query->orderByDesc('created_at'),
        };

        $perPage = min(max($request->integer('per_page', 12), 1), 50);

        return CourseResource::collection(
            $query->paginate($perPage)->withQueryString()
        );
    }

    public function store(StoreCourseRequest $request): CourseResource
    {
        $course = Course::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
            'status' => CourseStatus::Draft,
            'published_at' => null,
        ]);

        $course->load(['category', 'creator', 'courseInstructors.instructor']);

        return new CourseResource($course);
    }

    public function show(Request $request, Course $course): CourseResource
    {
        if ($course->status !== CourseStatus::Published) {
            abort_unless(
                $request->user()
                    && $this->canManageCourse($request->user(), $course),
                404
            );
        }

        $course->load([
            'category',
            'creator',
            'courseInstructors.instructor',
            'modules',
        ])->loadCount('modules');

        return new CourseResource($course);
    }

    public function update(
        UpdateCourseRequest $request,
        Course $course
    ): CourseResource {
        $course->update($request->validated());

        $course->load([
            'category',
            'creator',
            'courseInstructors.instructor',
        ]);

        return new CourseResource($course->refresh());
    }

    public function destroy(Course $course): JsonResponse
    {
        $this->authorize('delete', $course);

        $course->delete();

        return response()->json([
            'message' => 'Course deleted successfully.',
        ]);
    }

    public function submitForReview(Course $course): CourseResource
    {
        $this->authorize('update', $course);

        abort_unless(
            in_array($course->status, [CourseStatus::Draft, CourseStatus::Review], true),
            422,
            'Only draft or review courses can be submitted for review.'
        );

        $course->update([
            'status' => CourseStatus::Review,
            'published_at' => null,
        ]);

        return new CourseResource(
            $course->refresh()->load([
                'category',
                'creator',
                'courseInstructors.instructor',
            ])
        );
    }

    public function publish(
        PublishCourseRequest $request,
        Course $course
    ): CourseResource {
        abort_unless(
            in_array($course->status, [CourseStatus::Review, CourseStatus::Published], true),
            422,
            'Only courses in review can be published.'
        );

        $course->update([
            'status' => CourseStatus::Published,
            'published_at' => $course->published_at ?? now(),
        ]);

        return new CourseResource(
            $course->refresh()->load([
                'category',
                'creator',
                'courseInstructors.instructor',
            ])
        );
    }

    private function canManageCourse($user, Course $course): bool
    {
        return $user->hasRole('admin')
            || $course->created_by === $user->id
            || $course->instructors()->whereKey($user->id)->exists();
    }
}

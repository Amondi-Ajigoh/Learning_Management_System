<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CourseCategory\StoreCourseCategoryRequest;
use App\Http\Requests\Api\V1\CourseCategory\UpdateCourseCategoryRequest;
use App\Http\Resources\Api\V1\CourseCategoryResource;
use App\Models\CourseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class CourseCategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = CourseCategory::query()
            ->where('is_active', true)
            ->withCount([
                'courses' => fn ($query) => $query->where('status', 'published'),
            ])
            ->orderBy('name')
            ->get();

        return CourseCategoryResource::collection($categories);
    }

    public function store(StoreCourseCategoryRequest $request): CourseCategoryResource
    {
        $category = CourseCategory::create($request->validated());

        return new CourseCategoryResource($category);
    }

    public function show(CourseCategory $courseCategory): CourseCategoryResource
    {
        $this->authorize('view', $courseCategory);

        $courseCategory->loadCount([
            'courses' => fn ($query) => $query->where('status', 'published'),
        ]);

        return new CourseCategoryResource($courseCategory);
    }

    public function update(
        UpdateCourseCategoryRequest $request,
        CourseCategory $courseCategory
    ): CourseCategoryResource {
        $courseCategory->update($request->validated());

        return new CourseCategoryResource($courseCategory->refresh());
    }

    public function destroy(CourseCategory $courseCategory): JsonResponse
    {
        $this->authorize('delete', $courseCategory);

        if ($courseCategory->courses()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a category that contains courses.',
            ], 409);
        }

        $courseCategory->delete();

        return response()->json([
            'message' => 'Course category deleted successfully.',
        ]);
    }
}

<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\CourseCategoryController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\CourseInstructorController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:api')
            ->name('api.v1.auth.register');

        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:api')
            ->name('api.v1.auth.login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])
                ->name('api.v1.auth.me');

            Route::post('/logout', [AuthController::class, 'logout'])
                ->name('api.v1.auth.logout');
        });
    });

    Route::get('/categories', [CourseCategoryController::class, 'index'])
        ->name('api.v1.categories.index');

    Route::get('/categories/{courseCategory}', [CourseCategoryController::class, 'show'])
        ->name('api.v1.categories.show');

    Route::get('/courses', [CourseController::class, 'index'])
        ->name('api.v1.courses.index');

    Route::get('/courses/{course}', [CourseController::class, 'show'])
        ->name('api.v1.courses.show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/categories', [CourseCategoryController::class, 'store'])
            ->name('api.v1.categories.store');

        Route::match(['put', 'patch'], '/categories/{courseCategory}', [CourseCategoryController::class, 'update'])
            ->name('api.v1.categories.update');

        Route::delete('/categories/{courseCategory}', [CourseCategoryController::class, 'destroy'])
            ->name('api.v1.categories.destroy');

        Route::post('/courses', [CourseController::class, 'store'])
            ->name('api.v1.courses.store');

        Route::match(['put', 'patch'], '/courses/{course}', [CourseController::class, 'update'])
            ->name('api.v1.courses.update');

        Route::delete('/courses/{course}', [CourseController::class, 'destroy'])
            ->name('api.v1.courses.destroy');

        Route::post('/courses/{course}/submit-for-review', [CourseController::class, 'submitForReview'])
            ->name('api.v1.courses.submit-for-review');

        Route::post('/courses/{course}/publish', [CourseController::class, 'publish'])
            ->name('api.v1.courses.publish');

        Route::get('/courses/{course}/instructors', [CourseInstructorController::class, 'index'])
            ->name('api.v1.courses.instructors.index');

        Route::post('/courses/{course}/instructors', [CourseInstructorController::class, 'store'])
            ->name('api.v1.courses.instructors.store');

        Route::match(
            ['put', 'patch'],
            '/courses/{course}/instructors/{courseInstructor}',
            [CourseInstructorController::class, 'update']
        )->name('api.v1.courses.instructors.update');

        Route::delete(
            '/courses/{course}/instructors/{courseInstructor}',
            [CourseInstructorController::class, 'destroy']
        )->name('api.v1.courses.instructors.destroy');
    });
});

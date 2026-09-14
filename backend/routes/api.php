<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
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
});

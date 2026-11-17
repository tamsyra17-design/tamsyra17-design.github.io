<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/v1/health', HealthController::class);

Route::prefix('v1/auth')->group(function () {
    // Both endpoints are unauthenticated writes, so they are throttled by IP:
    // registration is looser (shared NATs) than login, which is the endpoint
    // credential stuffing would attack.
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::get('/v1/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

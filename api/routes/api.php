<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/v1/health', HealthController::class);

Route::get('/v1/categories', [CategoryController::class, 'index']);

Route::get('/v1/products', [ProductController::class, 'index']);
Route::get('/v1/products/{slug}', [ProductController::class, 'show']);

Route::get('/v1/settings/public', [SettingController::class, 'public']);

Route::get('/v1/pages/{slug}', [PageController::class, 'show']);

// Guest checkout. Each order locks and decrements stock, so the endpoint gets
// its own throttle bucket to blunt scripted stock-hoarding.
Route::post('/v1/orders', [OrderController::class, 'store'])->middleware('throttle:10,1,orders-store');

Route::prefix('v1/auth')->group(function () {
    // Both endpoints are unauthenticated writes, so each gets its own named
    // throttle bucket: registration is looser (shared NATs) than login, which
    // is what credential stuffing attacks. Without the name both routes hash to
    // the same sha1(domain|ip) key and one endpoint's traffic 429s the other.
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1,auth-register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1,auth-login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::get('/v1/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

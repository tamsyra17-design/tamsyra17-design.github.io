<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\PaymentWebhookController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\RentalAvailabilityController;
use App\Http\Controllers\Api\V1\RentalController;
use App\Http\Controllers\Api\V1\RentalQuoteController;
use App\Http\Controllers\Api\V1\ShippingRateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/v1/health', HealthController::class);

Route::get('/v1/categories', [CategoryController::class, 'index']);

Route::get('/v1/products', [ProductController::class, 'index']);
Route::get('/v1/products/{slug}', [ProductController::class, 'show']);

Route::get('/v1/rental/products/{slug}/availability', [RentalAvailabilityController::class, 'show']);

Route::get('/v1/settings/public', [SettingController::class, 'public']);

Route::get('/v1/pages/{slug}', [PageController::class, 'show']);

// Guest checkout. Each order locks and decrements stock, so the endpoint gets
// its own throttle bucket to blunt scripted stock-hoarding.
Route::post('/v1/orders', [OrderController::class, 'store'])->middleware('throttle:10,1,orders-store');

// Guest tracking: number + phone. Throttled to slow number/phone guessing.
Route::get('/v1/orders/{number}', [OrderController::class, 'show'])->middleware('throttle:30,1,orders-show');

Route::post('/v1/rental/quote', [RentalQuoteController::class, 'store']);
Route::get('/v1/shipping/rates', [ShippingRateController::class, 'index']);
Route::post('/v1/payments/webhook/{gateway}', [PaymentWebhookController::class, 'handle']);
Route::post('/v1/rentals', [RentalController::class, 'store']);
Route::get('/v1/rentals/{number}', [RentalController::class, 'show']);

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

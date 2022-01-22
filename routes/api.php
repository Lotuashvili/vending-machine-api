<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BalanceController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserInfoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', UserInfoController::class);

    Route::apiResource('products', ProductController::class)->only(['index', 'show']);

    Route::middleware('role:Seller')->group(function () {
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    });

    Route::middleware('role:Buyer')->group(function () {
        Route::prefix('balance')->group(function () {
            Route::post('deposit', [BalanceController::class, 'deposit']);
            Route::post('reset', [BalanceController::class, 'reset']);
        });

        Route::post('products/buy/{product}', [ProductController::class, 'buy']);
    });
});

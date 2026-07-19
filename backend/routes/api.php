<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TikTokAuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::prefix('auth/tiktok')->group(function () {
    Route::get('/redirect', [TikTokAuthController::class, 'redirect']);
    Route::get('/callback', [TikTokAuthController::class, 'callback'])->name('tiktok.callback');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/daily-stats', [DashboardController::class, 'dailyStats']);

    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
});

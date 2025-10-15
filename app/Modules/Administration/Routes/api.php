<?php

use App\Modules\Administration\Controllers\UserAuthController;
use App\Modules\Administration\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth/')->group(function () {
    Route::post('/signup', [UserAuthController::class, 'signup']);
    Route::post('/login', [UserAuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->prefix('auth/')->group(function () {
    Route::post('/logout', [UserAuthController::class, 'logout']);
    Route::get('/users/auth', [UserController::class, 'authUser']);

    Route::put('/users/password', [UserController::class, 'updatePassword']);
    Route::apiResource('/users', UserController::class)->except(['store']);
});

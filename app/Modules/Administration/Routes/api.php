<?php

use App\Modules\Administration\Controllers\AccessController;
use App\Modules\Administration\Controllers\FournisseurController;
use App\Modules\Administration\Controllers\UserAuthController;
use App\Modules\Administration\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->group(function () {
    Route::post('/signup', [UserAuthController::class, 'signup']);
    Route::post('/login', [UserAuthController::class, 'login']);
});


Route::middleware('auth:sanctum')->prefix('v1/auth')->group(function () {
    Route::post('/logout', [UserAuthController::class, 'logout']);
    Route::get('/users/auth', [UserController::class, 'authUser']);

    Route::put('/users/password', [UserController::class, 'updatePassword']);
    Route::apiResource('/users', UserController::class)->except(['store']);
});


Route::middleware('auth:sanctum')->prefix('v1/admins')->group(function(){
    Route::get('fournisseurs/restore/{id}', [FournisseurController::class, 'restore']);
    Route::delete('fournisseurs/delete/{id}', [FournisseurController::class, 'forceDelete']);
    Route::apiResource("fournisseurs", FournisseurController::class);

    Route::post("accesses", [AccessController::class, 'store']);
});

Route::middleware('auth:sanctum')->prefix('v1/dashboard')->group(function(){
    Route::get('/statistique', [UserController::class, 'statistic']);
    Route::get('/fixings-hebdomadaire', [UserController::class, 'weeklyFixings']);
    Route::get('/fixings-journalier', [UserController::class, 'dailyFixings']);
    Route::get('/transaction-journaliere', [UserController::class, 'dailyOperations']);
});

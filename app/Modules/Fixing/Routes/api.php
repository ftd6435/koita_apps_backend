<?php

use App\Modules\Fixing\Controllers\FixingBarreController;
use App\Modules\Fixing\Controllers\ExpeditionController;
use App\Modules\Fixing\Controllers\FixingController;
use Illuminate\Support\Facades\Route;
use App\Modules\Fixing\Controllers\InitLivraisonController;

Route::middleware('auth:sanctum')->prefix('v1/fixings')->group(function () {
    Route::apiResource('init-livraisons', InitLivraisonController::class);

    Route::get('fixing-fournisseurs/restore/{id}', [FixingController::class, 'restore']);
    Route::put('fixing-fournisseurs/status/{id}', [FixingController::class, 'status']);
    Route::delete('fixing-fournisseurs/delete/{id}', [FixingController::class, 'forceDelete']);
    Route::apiResource('fixing-fournisseurs', FixingController::class);

    Route::get('fixing-barre-fournisseurs/restore/{id}', [FixingBarreController::class, 'restore']);
    Route::delete('fixing-barre-fournisseurs/delete/{id}', [FixingBarreController::class, 'forceDelete']);
    Route::apiResource('fixing-barre-fournisseurs', FixingBarreController::class)->except(['store', 'update']);
});


Route::middleware('auth:sanctum')->prefix('v1/livraisons')->group(function () {
    Route::apiResource('expeditions', ExpeditionController::class);
    
});
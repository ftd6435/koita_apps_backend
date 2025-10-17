<?php

use App\Modules\Fixing\Controllers\FixingController;
use Illuminate\Support\Facades\Route;
use App\Modules\Fixing\Controllers\InitLivraisonController;

Route::middleware('auth:sanctum')->prefix('v1/fixings')->group(function () {
    Route::apiResource('init-livraisons', InitLivraisonController::class);

    Route::get('fixing-fournisseurs/restore/{id}', [FixingController::class, 'restore']);
    Route::put('fixing-fournisseurs/status/{id}', [FixingController::class, 'status']);
    Route::delete('fixing-fournisseurs/delete/{id}', [FixingController::class, 'forceDelete']);
    Route::apiResource('fixing-fournisseurs', FixingController::class);
});

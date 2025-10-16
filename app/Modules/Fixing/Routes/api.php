<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Fixing\Controllers\InitLivraisonController;

Route::middleware('auth:sanctum')->prefix('v1/fixings')->group(function () {
    Route::apiResource('init-livraisons', InitLivraisonController::class);
});

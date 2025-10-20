<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Settings\Controllers\ClientController;
use App\Modules\Settings\Controllers\DeviseController;

Route::middleware('auth:sanctum')->prefix('v1/settings/')->group(function () {
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('devises', DeviseController::class);
});

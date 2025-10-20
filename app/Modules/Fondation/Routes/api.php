<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Fondation\Controllers\FondationController;
use App\Modules\Fondation\Controllers\FondationDubaiController;
use App\Modules\Fondation\Controllers\InitFondationController;


Route::middleware('auth:sanctum')->prefix('v1/fondations')->group(function () {
    Route::apiResource('operations', FondationController::class)->only([
        'index',
        'store',
        'show',
        'destroy',
    ]);
    Route::get('liste-non-fondu',[FondationController::class,'listeFondationNonFondue']);
    Route::apiResource('init-fondations', InitFondationController::class)->only([
        'index',
        'show',
        'destroy',
    ]);
     Route::put('dubai/corrections', [FondationDubaiController::class, 'updateCorrections']);
});

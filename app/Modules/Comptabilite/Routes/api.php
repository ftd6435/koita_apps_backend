<?php

use App\Modules\Comptabilite\Controllers\CompteController;
use App\Modules\Comptabilite\Controllers\FournisseurOperationController;
use Illuminate\Support\Facades\Route;
use App\Modules\Comptabilite\Controllers\TypeOperationController;

Route::middleware('auth:sanctum')->prefix('comptabilite')->group(function () {
    Route::apiResource('type-operations', TypeOperationController::class);

    // Routes des comptes
    Route::get('comptes/restore/{id}', [CompteController::class, 'restore']);
    Route::delete('comptes/delete/{id}', [CompteController::class, 'forceDelete']);
    Route::apiResource('comptes', CompteController::class);

    // Routes des operations des fournisseurs
    Route::get('fournisseur-operations/restore/{id}', [FournisseurOperationController::class, 'restore']);
    Route::delete('fournisseur-operations/delete/{id}', [FournisseurOperationController::class, 'forceDelete']);
    Route::apiResource('fournisseur-operations', FournisseurOperationController::class);
});

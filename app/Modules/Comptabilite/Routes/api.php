<?php

use App\Modules\Comptabilite\Controllers\CaisseController;
use App\Modules\Comptabilite\Controllers\CompteController;
use App\Modules\Comptabilite\Controllers\FournisseurOperationController;
use App\Modules\Comptabilite\Controllers\OperationClientController;
use App\Modules\Comptabilite\Controllers\OperationDiversController;
use Illuminate\Support\Facades\Route;
use App\Modules\Comptabilite\Controllers\TypeOperationController;
use App\Modules\Comptabilite\Models\CompteDevise;

Route::middleware('auth:sanctum')->prefix('v1/comptabilite')->group(function () {
    Route::apiResource('type-operations', TypeOperationController::class);
     Route::apiResource('caisse', CaisseController::class);
    Route::apiResource('operations-clients', OperationClientController::class);
    Route::apiResource('operations-divers', OperationDiversController::class);

    // Routes des comptes
    Route::get('comptes/restore/{id}', [CompteController::class, 'restore']);
    Route::delete('comptes/delete/{id}', [CompteController::class, 'forceDelete']);
    Route::apiResource('comptes', CompteController::class);

    // Routes des operations des fournisseurs
    Route::get('fournisseur-operations/restore/{id}', [FournisseurOperationController::class, 'restore']);
    Route::delete('fournisseur-operations/delete/{id}', [FournisseurOperationController::class, 'forceDelete']);
    Route::apiResource('fournisseur-operations', FournisseurOperationController::class);

    // Routes de liaison de compte a devise
    Route::get('compte-devise/comptes/{id}', [CompteDevise::class, 'comptes']);
    Route::apiResource('compte-devise', CompteDevise::class);
});

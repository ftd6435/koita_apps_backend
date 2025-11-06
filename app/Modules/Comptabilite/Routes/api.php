<?php

use App\Modules\Comptabilite\Controllers\BanqueController;
use App\Modules\Comptabilite\Controllers\CaisseController;
use App\Modules\Comptabilite\Controllers\CompteController;
use App\Modules\Comptabilite\Controllers\FournisseurOperationController;
use App\Modules\Comptabilite\Controllers\OperationClientController;
use App\Modules\Comptabilite\Controllers\OperationDiversController;
use Illuminate\Support\Facades\Route;
use App\Modules\Comptabilite\Controllers\TypeOperationController;

Route::middleware('auth:sanctum')->prefix('v1/comptabilite')->group(function () {
    Route::apiResource('type-operations', TypeOperationController::class);
     Route::apiResource('caisse', CaisseController::class);
    Route::apiResource('operations-clients', OperationClientController::class);
    Route::apiResource('operations-divers', OperationDiversController::class);

    // Routes des comptes
    Route::get('banques/restore/{id}', [BanqueController::class, 'restore']);
    Route::delete('banques/delete/{id}', [BanqueController::class, 'forceDelete']);
    Route::apiResource('banques', BanqueController::class);

    // Routes des operations des fournisseurs
    Route::get('fournisseur-operations/restore/{id}', [FournisseurOperationController::class, 'restore']);
    Route::delete('fournisseur-operations/delete/{id}', [FournisseurOperationController::class, 'forceDelete']);
    Route::apiResource('fournisseur-operations', FournisseurOperationController::class);

    // Routes de liaison de compte a devise
    Route::apiResource('comptes', CompteController::class);

    Route::get('operations/historique', [BanqueController::class, 'operations']);
});

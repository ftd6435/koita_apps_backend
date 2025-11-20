<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Settings\Controllers\ClientController;
use App\Modules\Settings\Controllers\DeviseController;
use App\Modules\Settings\Controllers\DiversController;

Route::middleware('auth:sanctum')->prefix('v1/settings/')->group(function () {
   
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('devises', DeviseController::class);
    
    Route::apiResource('divers', DiversController::class);
    Route::get('releve-periode/clients/{id_client}', [ClientController::class, 'getReleveClientPeriode']);
     Route::get('clients/fixings-provisoire/{id_clien}',[ClientController::class,'getFixingsProvisoiresByClient']);
     Route::get('solde-global/divers', [DiversController::class, 'soldeGlobal']);
     Route::get('solde-global/clients', [ClientController::class, 'soldeGlobal']);

    
    //truncateDatabaseExcept
    Route::delete('vider-database', [ClientController::class,'truncateDatabaseExcept']);
});

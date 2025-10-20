<?php

namespace App\Modules\Comptabilite\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comptabilite\Models\Compte;
use App\Modules\Comptabilite\Requests\StoreCompteRequest;
use App\Modules\Comptabilite\Resources\CompteResource;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class CompteController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $comptes = Compte::with('fournisseurOperations', 'createdBy', 'updatedBy')->get();

        return $this->successResponse(CompteResource::collection($comptes), "Liste des comptes chargé avec succès.");
    }

    public function show(string $id)
    {
        $compte = Compte::with('fournisseurOperations', 'createdBy', 'updatedBy')->find($id);

        if(! $compte){
            return $this->errorResponse("Compte introuvable");
        }

        return $this->successResponse(new CompteResource($compte), "Compte demandé bien chargé.");
    }

    public function store(StoreCompteRequest $request)
    {
        $fields = $request->validated();
        $fields['created_by'] = Auth::id();

        $compte = Compte::create($fields);

        return $this->successResponse($compte, "Nouveau compte ajouté avec succès.");
    }

    public function update(StoreCompteRequest $request, string $id)
    {
        $compte = Compte::find($id);

        if(! $compte){
            return $this->errorResponse("Compte introuvable");
        }

        $fields = $request->validated();
        $fields['updated_by'] = Auth::id();

        $compte->update($fields);

        return $this->successResponse($compte, "Compte mis a jour avec succès.");
    }

    public function destroy(string $id)
    {
        $compte = Compte::find($id);

        if(! $compte){
            return $this->errorResponse("Compte introuvable");
        }

        $compte->delete();

        return $this->deleteSuccessResponse("Compte déplacé vers la corbeille avec succès.");
    }

    public function restore(string $id)
    {
        $compte = Compte::withTrashed()->find($id);

        if(! $compte){
            return $this->errorResponse("Compte introuvable");
        }

        $compte->restore();

        return $this->deleteSuccessResponse("Compte restoré avec succès.");
    }

    public function forceDelete(string $id)
    {
         $compte = Compte::withTrashed()->find($id);

        if(! $compte){
            return $this->errorResponse("Compte introuvable");
        }

        $compte->forceDelete();

        return $this->deleteSuccessResponse("Compte supprimé définitivement avec succès.");
    }
}

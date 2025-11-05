<?php

namespace App\Modules\Comptabilite\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comptabilite\Models\Compte;
use App\Modules\Comptabilite\Requests\CompteRequest;
use App\Modules\Comptabilite\Resources\CompteResource;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class CompteDeviseController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $comptes = Compte::with('banque', 'devise', 'createdBy', 'updatedBy')->orderBy('created_at', 'desc')->get();

        return $this->successResponse(CompteResource::collection($comptes), 'Liste des comptes bien chargé.');
    }

    public function show(string $id)
    {
        $compte = Compte::find($id);

        if(! $compte){
            return $this->errorResponse("Compte introuvable.");
        }

        return $this->successResponse(new CompteResource($compte), 'Compte demandé bien chargé.');
    }

    public function store(CompteRequest $request)
    {
        $fields = $request->validated();
        $fields['created_by'] = Auth::id();

        $compte = Compte::create($fields);

        return $this->successResponse($compte, "Compte créé avec succès.");
    }

    public function update(CompteRequest $request, string $id)
    {
        $compte = Compte::find($id);

        if(! $compte){
            return $this->errorResponse("Compte introuvable.");
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
            return $this->errorResponse("Compte introuvable.");
        }

        $compte->delete();

        return $this->deleteSuccessResponse("Compte supprimé avec succès.");
    }
}

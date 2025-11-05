<?php

namespace App\Modules\Comptabilite\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comptabilite\Models\Banque;
use App\Modules\Comptabilite\Requests\StoreBanqueRequest;
use App\Modules\Comptabilite\Resources\BanqueResource;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class BanqueController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $banques = Banque::with('fournisseurOperations', 'createdBy', 'updatedBy')->get();

        return $this->successResponse(BanqueResource::collection($banques), "Liste des banques chargé avec succès.");
    }

    public function show(string $id)
    {
        $banque = Banque::with('fournisseurOperations', 'createdBy', 'updatedBy')->find($id);

        if(! $banque){
            return $this->errorResponse("Banque introuvable");
        }

        return $this->successResponse(new BanqueResource($banque), "Banque demandé bien chargé.");
    }

    public function store(StoreBanqueRequest $request)
    {
        $fields = $request->validated();
        $fields['created_by'] = Auth::id();

        $banque = Banque::create($fields);

        return $this->successResponse($banque, "Nouvelle banque ajouté avec succès.");
    }

    public function update(StoreBanqueRequest $request, string $id)
    {
        $banque = Banque::find($id);

        if(! $banque){
            return $this->errorResponse("Banque introuvable");
        }

        $fields = $request->validated();
        $fields['updated_by'] = Auth::id();

        $banque->update($fields);

        return $this->successResponse($banque, "Banque mise a jour avec succès.");
    }

    public function destroy(string $id)
    {
        $banque = Banque::find($id);

        if(! $banque){
            return $this->errorResponse("Banque introuvable");
        }

        $banque->delete();

        return $this->deleteSuccessResponse("Banque déplacé vers la corbeille avec succès.");
    }

    public function restore(string $id)
    {
        $banque = Banque::withTrashed()->find($id);

        if(! $banque){
            return $this->errorResponse("Banque introuvable");
        }

        $banque->restore();

        return $this->deleteSuccessResponse("Banque restoré avec succès.");
    }

    public function forceDelete(string $id)
    {
         $banque = Banque::withTrashed()->find($id);

        if(! $banque){
            return $this->errorResponse("Banque introuvable");
        }

        $banque->forceDelete();

        return $this->deleteSuccessResponse("Banque supprimé définitivement avec succès.");
    }
}

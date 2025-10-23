<?php

namespace App\Modules\Comptabilite\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comptabilite\Models\FournisseurOperation;
use App\Modules\Comptabilite\Requests\StoreFournisseurOperationRequest;
use App\Modules\Comptabilite\Resources\FournisseurOperationResource;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class FournisseurOperationController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $fournisseur_operations = FournisseurOperation::with('fournisseur', 'typeOperation', 'devise', 'createdBy', 'updatedBy')->orderBy('created_at', 'desc')->get();

        return $this->successResponse(FournisseurOperationResource::collection($fournisseur_operations), "Liste de toutes les operations des fournisseurs.");
    }

    public function show(string $id)
    {
        $fournisseur_operation = FournisseurOperation::with('fournisseur', 'typeOperation', 'devise', 'createdBy', 'updatedBy')->find($id);

        if(! $fournisseur_operation){
            return $this->errorResponse("Operation fournisseur introuvable");
        }

        return $this->successResponse(new FournisseurOperationResource($fournisseur_operation), "Operation du fournisseur demandé bien chargé.");
    }

    public function store(StoreFournisseurOperationRequest $request)
    {
        $fields = $request->validated();
        $fields['created_by'] = Auth::id();

        if(is_null($fields['reference'])){
            $fields['reference'] = 'REF' . round(1000, 9999);
        }

        $fournisseur_operation = FournisseurOperation::create($fields);

        return $this->successResponse($fournisseur_operation, "Operation du fournisseur enrégistrée avec succès.");
    }

    public function update(StoreFournisseurOperationRequest $request, string $id)
    {
        $fournisseur_operation = FournisseurOperation::find($id);

        if(! $fournisseur_operation){
            return $this->errorResponse("Operation fournisseur introuvable");
        }

        $fields = $request->validated();
        $fields['updated_by'] = Auth::id();

        $fournisseur_operation->update($fields);

        return $this->successResponse($fournisseur_operation, "Operation du fournisseur mise a jour avec succès.");
    }

    public function destroy(string $id)
    {
        $fournisseur_operation = FournisseurOperation::find($id);

        if(! $fournisseur_operation){
            return $this->errorResponse("Operation fournisseur introuvable");
        }

        $fournisseur_operation->delete();

        return $this->deleteSuccessResponse("Operation du fournisseur déplacé vers la corbeille avec succès.");
    }

    public function restore(string $id)
    {
        $fournisseur_operation = FournisseurOperation::withTrashed()->find($id);

        if(! $fournisseur_operation){
            return $this->errorResponse("Operation fournisseur introuvable");
        }

        $fournisseur_operation->restore();

        return $this->deleteSuccessResponse("Operation du fournisseur restorée avec succès.");
    }

    public function forceDelete(string $id)
    {
        $fournisseur_operation = FournisseurOperation::withTrashed()->find($id);

        if(! $fournisseur_operation){
            return $this->errorResponse("Operation fournisseur introuvable");
        }

        $fournisseur_operation->forceDelete();

        return $this->deleteSuccessResponse("Operation du fournisseur supprimée définitivement avec succès.");
    }
}

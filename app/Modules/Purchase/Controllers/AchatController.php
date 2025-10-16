<?php

namespace App\Modules\Purchase\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Purchase\Models\Achat;
use App\Modules\Purchase\Requests\StoreAchatRequest;
use App\Modules\Purchase\Resources\AchatResource;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class AchatController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $achats = Achat::with('fournisseur', 'lot', 'barres', 'createdBy', 'updatedBy')->orderBy('created_at', 'desc')->get();

        return $this->successResponse(AchatResource::collection($achats), 'Liste de tous les achats');
    }

    public function show(string $id)
    {
        $achat = Achat::with('fournisseur', 'lot', 'barres', 'createdBy', 'updatedBy')->find($id);

        if (!$achat) {
            return $this->errorResponse('Achat introuvable');
        }

        return $this->successResponse(new AchatResource($achat), 'Achat demandé bien chargé.');
    }

    public function status(string $id)
    {
        $achat = Achat::find($id);

        if (!$achat) {
            return $this->errorResponse('Achat introuvable');
        }

        $achat->status = ($achat->status == 'encours') ? 'terminer' : 'encours';
        $achat->save();

        return $this->deleteSuccessResponse("Status d'achat mis a jour avec succès.");
    }

    public function etat(string $id)
    {
        $achat = Achat::find($id);

        if (!$achat) {
            return $this->errorResponse('Achat introuvable');
        }

        $achat->etat = ($achat->etat == 'non fondue') ? 'fondue' : 'non fondue';
        $achat->save();

        return $this->deleteSuccessResponse("Etat d'achat mis a jour avec succès.");
    }

    public function store(StoreAchatRequest $request)
    {
        $fields = $request->validated();
        $fields['created_by'] = Auth::id();

        if (is_null($request->reference)) {
            $fields['reference'] = 'AC'.rand(1000, 9999);
        }

        $achat = Achat::create($fields);

        return $this->successResponse($achat, 'Nouveau achat ajouté avec succès.');
    }

    public function update(StoreAchatRequest $request, string $id)
    {
        $achat = Achat::find($id);

        if (!$achat) {
            return $this->errorResponse('Achat introuvable');
        }

        $fields = $request->validated();
        $fields['updated_by'] = Auth::id();

        $achat->update($fields);

        return $this->successResponse($achat, 'Achat mis a jour avec succès.');
    }

    public function destroy(string $id)
    {
        $achat = Achat::find($id);

        if (!$achat) {
            return $this->errorResponse('Achat introuvable');
        }

        $achat->delete();

        return $this->deleteSuccessResponse('Achat déplacé vers la corbeille avec succès.');
    }

    public function restore(string $id)
    {
        $achat = Achat::withTrashed()->find($id);

        if (!$achat) {
            return $this->errorResponse('Achat introuvable');
        }

        $achat->restore();

        return $this->deleteSuccessResponse('Achat restoré avec succès.');
    }

    public function forceDelete(string $id)
    {
        $achat = Achat::withTrashed()->find($id);

        if (!$achat) {
            return $this->errorResponse('Achat introuvable');
        }

        $achat->forceDelete();

        return $this->deleteSuccessResponse('Achat supprimé définitivement avec succès.');
    }
}

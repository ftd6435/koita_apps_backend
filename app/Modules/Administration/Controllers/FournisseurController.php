<?php

namespace App\Modules\Administration\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Administration\Models\Fournisseur;
use App\Modules\Administration\Requests\StoreFournisseurRequest;
use App\Modules\Administration\Resources\FournisseurResource;
use App\Traits\ApiResponses;
use App\Traits\ImageUpload;
use Illuminate\Support\Facades\Auth;

class FournisseurController extends Controller
{
    use ApiResponses, ImageUpload;

    public function index()
    {
        $fournisseurs = Fournisseur::with('achats', 'operations', 'createdBy', 'updatedBy')->orderBy('created_by', 'desc')->get();

        return $this->successResponse(FournisseurResource::collection($fournisseurs), "Liste de tous les fournisseurs.");
    }

    public function show(string $id)
    {
        $fournisseur = Fournisseur::with('achats', 'operations', 'createdBy', 'updatedBy')->find($id);

        if(! $fournisseur){
            return $this->errorResponse("Fournisseur introuvable");
        }

        return $this->successResponse(new FournisseurResource($fournisseur), "Fournisseur demandé bien chargé.");
    }

    public function store(StoreFournisseurRequest $request)
    {
        $fields = $request->validated();
        $fields['created_by'] = Auth::id();

        if($request->hasFile('image')){
            $fields['image'] = $this->imageUpload($request->file('image'), "fournisseurs");
        }

        $fournisseur = Fournisseur::create($fields);

        return $this->successResponse($fournisseur, "Fournisseur ajouté avec succès.");
    }

    public function update(StoreFournisseurRequest $request, string $id)
    {
        $fournisseur = Fournisseur::find($id);

        if(! $fournisseur){
            return $this->errorResponse("Fournisseur introuvable");
        }

        $fields = $request->validated();
        $fields['updated_by'] = Auth::id();

        if($request->hasFile('image')){
            $this->deleteImage($fournisseur->image, "fournisseurs");
            $fields['image'] = $this->imageUpload($request->file('image'), "fournisseurs");
        }

        $fournisseur->update($fields);

        return $this->successResponse($fournisseur, "Fournisseur mis a jour avec succès.");
    }

    public function destroy(string $id)
    {
        $fournisseur = Fournisseur::find($id);

        if(! $fournisseur){
            return $this->errorResponse("Fournisseur introuvable");
        }

        $fournisseur->delete();

        return $this->deleteSuccessResponse("Fournisseur déplacé vers la corbeille avec succès.");
    }

    public function restore(string $id)
    {
        $fournisseur = Fournisseur::withTrashed()->find($id);

        if(! $fournisseur){
            return $this->errorResponse("Fournisseur introuvable");
        }

        $fournisseur->restore();

        return $this->deleteSuccessResponse("Fournisseur restoré avec succès.");
    }

    public function forceDelete(string $id)
    {
        $fournisseur = Fournisseur::withTrashed()->find($id);

        if(! $fournisseur){
            return $this->errorResponse("Fournisseur introuvable");
        }

        $this->deleteImage($fournisseur->image, "fournisseurs");
        $fournisseur->forceDelete();

        return $this->deleteSuccessResponse("Fournisseur supprimé définitivement avec succès.");
    }
}

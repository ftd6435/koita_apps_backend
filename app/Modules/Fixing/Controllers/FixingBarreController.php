<?php

namespace App\Modules\Fixing\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fixing\Models\FixingBarre;
use App\Modules\Fixing\Resources\FixingBarreResource;
use App\Traits\ApiResponses;

class FixingBarreController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $fixing_barres = FixingBarre::with('fixing', 'barre', 'createdBy', 'updatedBy')->orderBy('created_at', 'desc')->get();

        return $this->successResponse(FixingBarreResource::collection($fixing_barres), "Liste des fixings et barres assosiés bien chargé.");
    }

    public function show(string $id)
    {
        $fixing_barre = FixingBarre::with('fixing', 'barre', 'createdBy', 'updatedBy')->find($id);

        if(! $fixing_barre){
            return $this->errorResponse("Fixing et Barre introuvable");
        }

        return $this->successResponse(new FixingBarreResource($fixing_barre), "Fixing et barre associés demandé bien chargé.");
    }

    public function store()
    {
        // Code here...
    }

    public function update()
    {
        // Code here...
    }

    public function destroy(string $id)
    {
        $fixing_barre = FixingBarre::find($id);

        if(! $fixing_barre){
            return $this->errorResponse("Fixing et Barre introuvable");
        }

        $fixing_barre->delete();

        return $this->deleteSuccessResponse("Fixing et Barre déplacé vers la corbeille avec succès.");
    }

    public function restore(string $id)
    {
        $fixing_barre = FixingBarre::withTrashed()->find($id);

        if(! $fixing_barre){
            return $this->errorResponse("Fixing et Barre introuvable");
        }

        $fixing_barre->restore();

        return $this->deleteSuccessResponse("Fixing et Barre restoré avec succès.");
    }

    public function forceDelete(string $id)
    {
        $fixing_barre = FixingBarre::withTrashed()->find($id);

        if(! $fixing_barre){
            return $this->errorResponse("Fixing et Barre introuvable");
        }

        $fixing_barre->forceDelete();

        return $this->deleteSuccessResponse("Fixing et Barre restoré avec succès.");
    }
}

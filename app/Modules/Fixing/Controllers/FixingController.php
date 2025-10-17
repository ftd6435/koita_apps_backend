<?php

namespace App\Modules\Fixing\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fixing\Models\Fixing;
use App\Modules\Fixing\Models\FixingBarre;
use App\Modules\Fixing\Requests\StoreFixingRequest;
use App\Modules\Fixing\Resources\FixingResource;
use App\Modules\Purchase\Models\Barre;
use App\Modules\Settings\Models\Devise;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixingController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $fixings = Fixing::with('fournisseur', 'devise', 'fixingBarres', 'createdBy', 'updatedBy')->orderBy('created_at', 'desc')->get();

        return $this->successResponse(FixingResource::collection($fixings), "Liste de tous les fixings bien chargé.");
    }

    public function show(string $id)
    {
        $fixing = Fixing::with('fournisseur', 'devise', 'fixingBarres', 'createdBy', 'updatedBy')->find($id);

        if(! $fixing){
            return $this->errorResponse("Fixing introuvable");
        }

        return $this->successResponse(new FixingResource($fixing), "Fixing demandé bien chargé.");
    }

    public function status(Request $request, string $id)
    {
        $request->validate([
            'status' => "required|in:en attente,confirmer,valider"
        ]);

        $fixing = Fixing::find($id);

        if(! $fixing){
            return $this->errorResponse("Fixing introuvable");
        }

        $fixing->update(['status' => $request->status]);

        return $this->successResponse("Status du fixing mis a jour avec succès.");
    }

    public function store(StoreFixingRequest $request)
    {
        $fields = $request->validated();
        $fields['created_by'] = Auth::id();

        DB::beginTransaction();

        try {
            $devise = Devise::find($fields['devise_id']);

            if (Str::upper($devise->symbole) == 'USD') {
                $bourse = $fields['bourse'] ?? 0;
                $discount = $fields['discount'] ?? 0;

                $fields['unit_price'] = ($bourse / 34) - $discount;
            }

            $fixing = Fixing::create($fields);

            if(!empty($request->barres)){
                foreach($request->barres as $barre){
                    Barre::where('id', $barre['id'])->update(['is_fixed' => true]);

                    FixingBarre::create([
                        'fixing_id' => $fixing->id,
                        'barre_id' => $barre['id'],
                        'created_by' => Auth::id()
                    ]);
                }
            }

            DB::commit();

            return $this->successResponse($fixing, 'Fxing ajouté avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->errorResponse('Erreur lors de l\'ajout du fixing : '.$e->getMessage(), 500);
        }
    }

    public function update(StoreFixingRequest $request, string $id)
    {
        $fixing = Fixing::find($id);

        if(! $fixing){
            return $this->errorResponse('Fixing introuvable');
        }

        $fields = $request->validated();

        DB::beginTransaction();

        try {
            $devise = !empty($fields['devise_id'])
                        ? Devise::where('id', $fields['devise_id'])->value('symbole')
                        : optional($fixing->devise)->symbole;

            if ($devise && Str::upper($devise) == 'USD') {
                $bourse = $fields['bourse'] ?? 0;
                $discount = $fields['discount'] ?? 0;

                $fields['unit_price'] = ($bourse / 34) - $discount;
            }

            $fixing->update([
                'fournisseur_id' => $fields['fournisseur_id'] ?? $fixing->fournisseur_id,
                'poids_pro'      => $fields['poids_pro'] ?? $fixing->poids_pro,
                'carrat_moyenne' => $fields['carrat_moyenne'] ?? $fixing->carrat_moyenne,
                'discount'       => $fields['discount'] ?? $fixing->discount,
                'bourse'         => $fields['bourse'] ?? $fixing->bourse,
                'unit_price'     => $fields['unit_price'] ?? $fixing->unit_price,
                'devise_id'      => $fields['devise_id'] ?? $fixing->devise_id,
                'updated_by'     => Auth::id()
            ]);

            DB::commit();

            return $this->successResponse($fixing, 'Fxing mis a jour avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->errorResponse('Erreur lors de la mise a jou du fixing : '.$e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        $fixing = Fixing::find($id);

        if(! $fixing){
            return $this->errorResponse("Fixing introuvable");
        }

        foreach($fixing->fixingBarres as $fixingBarre){
            $fixingBarre->barre->update(['is_fixed' => false]);
            $fixingBarre->forceDelete();
        }

        $fixing->delete();

        return $this->deleteSuccessResponse("Fixing déplacé vers la corbeille avec succès.");
    }

    public function restore(string $id)
    {
        $fixing = Fixing::withTrashed()->find($id);

        if(! $fixing){
            return $this->errorResponse("Fixing introuvable");
        }

        $fixing->restore();

        return $this->deleteSuccessResponse("Fixing restoré avec succès.");
    }

    public function forceDelete(string $id)
    {
        $fixing = Fixing::withTrashed()->find($id);

        if(! $fixing){
            return $this->errorResponse("Fixing introuvable");
        }

        $fixing->forceDelete();

        return $this->deleteSuccessResponse("Fixing supprimé définitivement avec succès.");
    }
}

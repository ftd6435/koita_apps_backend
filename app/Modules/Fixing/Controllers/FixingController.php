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

    public function update()
    {
        // Code here...
    }

    public function destroy(string $id)
    {
        // Code here...
    }

    public function restore(string $id)
    {
        // Code here...
    }

    public function forceDelete(string $id)
    {
        // Code here...
    }
}

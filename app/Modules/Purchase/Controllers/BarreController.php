<?php

namespace App\Modules\Purchase\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Purchase\Models\Achat;
use App\Modules\Purchase\Models\Barre;
use App\Modules\Purchase\Models\Lot;
use App\Modules\Purchase\Requests\StoreBarreRequest;
use App\Modules\Purchase\Resources\BarreResource;
use App\Traits\ApiResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class BarreController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $barres = Barre::with('achat', 'createdBy', 'updatedBy')->orderBy('created_by', 'desc')->get();

        return $this->successResponse(BarreResource::collection($barres), "Liste de toutes les barres.");
    }

    public function show(string $id)
    {
        $barre = Barre::with('achat', 'createdBy', 'updatedBy')->find($id);

        if(! $barre){
            return $this->errorResponse("Barre introuvable");
        }

        return $this->successResponse(new BarreResource($barre), "Barre demandé bien chargé.");
    }

    public function store(StoreBarreRequest $request)
    {
        DB::beginTransaction();

        try {
            $barres = collect($request->validated());
            $now = Carbon::now();

            $insertData = [];
            $updateCount = 0;
            $insertCount = 0;

            foreach ($barres as $item) {
                // If ID exists → update existing barre
                if (!empty($item['id'])) {
                    Barre::where('id', $item['id'])->update([
                        'achat_id'   => $item['achat_id'],
                        'poid_pure'  => $item['poid_pure'],
                        'carrat_pure'=> $item['carrat_pure'],
                        'densite'    => $item['densite'] ?? null,
                        'updated_by' => Auth::id(),
                        'updated_at' => $now,
                    ]);
                    $updateCount++;
                }
                // Otherwise → prepare for insertion
                else {
                    $insertData[] = [
                        'achat_id'   => $item['achat_id'],
                        'poid_pure'  => $item['poid_pure'],
                        'carrat_pure'=> $item['carrat_pure'],
                        'densite'    => $item['densite'] ?? null,
                        'created_by' => Auth::id(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $insertCount++;
                }
            }

            // Bulk insert new barres
            if (!empty($insertData)) {
                Barre::insert($insertData);
            }

            DB::commit();

            $messageParts = [];
            if ($insertCount > 0) $messageParts[] = "$insertCount nouvelles barres ajoutées";
            if ($updateCount > 0) $messageParts[] = "$updateCount barres mises à jour";
            $message = implode(' et ', $messageParts) . ' avec succès.';

            return response()->json([
                'status' => 1,
                'message' => $message,
            ]);

        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 0,
                'message' => 'Une erreur est survenue lors de l’enregistrement des barres.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Barre $barre)
    {
        $validated = $request->validate([
            'achat_id' => ['sometimes', 'exists:achats,id'],
            'poid_pure' => ['sometimes', 'numeric', 'min:0'],
            'carrat_pure' => ['sometimes', 'numeric', 'min:0'],
            'densite' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $validated['updated_by'] = Auth::id();

        try {
            $barre->update($validated);

            return $this->successResponse($barre, 'La barre a été mise à jour avec succès.');
        } catch (Throwable $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Une erreur est survenue lors de la mise à jour.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $barre = Barre::find($id);

        if(! $barre){
            return $this->errorResponse("Barre introuvable");
        }

        $barre->delete();

        return $this->deleteSuccessResponse("Barre déplacé vers la corbeille avec succès.");
    }

    public function restore(string $id)
    {
        $barre = Barre::withTrashed()->find($id);

        if(! $barre){
            return $this->errorResponse("Barre introuvable");
        }

        $barre->restore();

        return $this->deleteSuccessResponse("Barre restoré avec succès.");
    }

    public function forceDelete(string $id)
    {
        $barre = Barre::withTrashed()->find($id);

        if(! $barre){
            return $this->errorResponse("Barre introuvable");
        }

        $barre->forceDelete();

        return $this->deleteSuccessResponse("Barre supprimée définitivement avec succès.");
    }
}

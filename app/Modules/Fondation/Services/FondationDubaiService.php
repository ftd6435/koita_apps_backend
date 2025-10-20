<?php

namespace App\Modules\Fondation\Services;

use App\Modules\Fondation\Models\Fondation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class FondationDubaiService
{
    public function updateCorrections(array $payload)
    {
        DB::beginTransaction();

        try {
            $updated = [];

            foreach ($payload['corrections'] as $item) {
                $fondation = Fondation::find($item['id']);

                if ($fondation) {
                    $fondation->update([
                        'poids_dubai'  => $item['poids_dubai'],
                        'carrat_dubai' => $item['carrat_dubai'],
                         'statut'=>'corriger',
                        'modify_by'    => Auth::id(),
                    ]);
                    $updated[] = $fondation;
                }
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Corrections de Dubaï appliquées avec succès.',
                'total'   => count($updated),
                'data'    => $updated,
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour des corrections de Dubaï.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}

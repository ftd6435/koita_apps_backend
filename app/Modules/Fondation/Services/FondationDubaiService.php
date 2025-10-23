<?php
namespace App\Modules\Fondation\Services;

use App\Modules\Fondation\Models\Fondation;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FondationDubaiService
{
    public function updateCorrections(array $payload)
    {
        DB::beginTransaction();

        try {
            $updated = [];

            foreach ($payload['corrections'] as $item) {
                $fondation = Fondation::with('initFondation')->find($item['id']); // on charge aussi la relation

                if ($fondation) {
                    // 🔹 Mise à jour de la fondation
                    $fondation->update([
                        'poids_dubai'  => $item['poids_dubai'],
                        'carrat_dubai' => $item['carrat_dubai'],
                        'statut'       => 'corriger',
                        'modify_by'    => Auth::id(),
                    ]);

                    // 🔹 Mise à jour du statut de la livraison liée
                    if ($fondation->initFondation) {
                        $initLivraison = $fondation->initFondation;
                        $initLivraison->update(['statut' => 'terminer']);
                    }

                    $updated[] = $fondation;
                }
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Corrections de Dubaï appliquées avec succès. Les livraisons associées ont été terminées.',
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

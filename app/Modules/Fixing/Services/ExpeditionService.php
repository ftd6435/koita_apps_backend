<?php
namespace App\Modules\Fixing\Services;

use App\Modules\Fixing\Models\Expedition;
use App\Modules\Fixing\Models\InitLivraison;
use App\Modules\Fixing\Resources\ExpeditionResource;
use App\Modules\Fondation\Models\Fondation;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpeditionService
{
    /**
     * 🔹 Créer une expédition complète
     * - Génère automatiquement une InitLivraison
     * - Crée plusieurs expéditions liées
     */
    public function store(array $payload)
    {
        DB::beginTransaction();

        try {

            // ✅ 2️⃣ Création automatique de l’init livraison
            $initLivraison = InitLivraison::create([
                'id_client'  => $payload['id_client'],
                'statut'     => 'encours',
                'created_by' => Auth::id(),
            ]);

            // ✅ 3️⃣ Création des expéditions liées
            $expeditions = collect();

            foreach ($payload['id_barre_fondu'] as $idFondation) {
                // Vérifie que la fondation existe
                $fondation = Fondation::find($idFondation);

                // ✅ Met à jour la fondation : marquée comme fixée
                $fondation->update(['is_fixed' => true]);

                // ✅ Crée l’expédition liée à la fondation
                $expedition = Expedition::create([
                    'id_barre_fondu'    => $idFondation,
                    'id_init_livraison' => $initLivraison->id,
                    'created_by'        => Auth::id(),
                ]);

                $expeditions->push($expedition);
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Expédition(s) créée(s) avec succès.',

            ]);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de l’expédition.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Lister toutes les expéditions
     */
    public function getAll()
    {
        try {
            $expeditions = Expedition::with(['fondation', 'initLivraison.client', 'createur', 'modificateur'])
                ->orderByDesc('id')
                ->get();

            return response()->json([
                'status'  => 200,
                'message' => 'Liste des expéditions récupérée avec succès.',
                'data'    => ExpeditionResource::collection($expeditions),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des expéditions.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    

    /**
     * 🔹 Récupérer une expédition spécifique
     */
    public function getOne(int $id)
    {
        try {
            $expedition = Expedition::with(['fondation', 'initLivraison.client', 'createur', 'modificateur'])
                ->find($id);

            if (! $expedition) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Expédition non trouvée.',
                ], 404);
            }

            return response()->json([
                'status'  => 200,
                'message' => 'Expédition récupérée avec succès.',
                'data'    => new ExpeditionResource($expedition),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération de l’expédition.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Supprimer une expédition
     */
    public function delete(int $id)
    {
        try {
            $expedition = Expedition::find($id);

            if (! $expedition) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Expédition non trouvée.',
                ], 404);
            }

            $expedition->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Expédition supprimée avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de l’expédition.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}

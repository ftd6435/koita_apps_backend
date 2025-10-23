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

    public function calculerPoidsEtCarat(int $id_init_livraison): array
    {
        // 🔹 Récupérer toutes les expéditions liées avec leurs fondations
        $expeditions = Expedition::where('id_init_livraison', $id_init_livraison)
            ->with('fondation')
            ->get();

        if ($expeditions->isEmpty()) {
            return [
                'poids_total'   => 0,
                'carrat_moyen'  => 0,
                'purete_totale' => 0,
                'details'       => [],
            ];
        }

        // === Étape 1 : Calcul du poids total et du carat moyen ===
        $poidsTotal         = 0.0;
        $sommeCaratPonderee = 0.0;

        foreach ($expeditions as $expedition) {
            if ($expedition->fondation) {
                $poids = (float) $expedition->fondation->poids_fondu;
                $carat = (float) $expedition->fondation->carrat_fondu;

                $poidsTotal += $poids;
                $sommeCaratPonderee += $poids * $carat;
            }
        }

        $carratMoyen = $poidsTotal > 0 ? $sommeCaratPonderee / $poidsTotal : 0.0;
        $carratMoyen = round($carratMoyen, 2);
        $poidsTotal  = round($poidsTotal, 3);

        // === Étape 2 : Calcul des puretés par ligne ===
        $details = [];
        foreach ($expeditions as $expedition) {
            if ($expedition->fondation) {
                $poids_fondu  = (float) $expedition->fondation->poids_fondu;
                $carrat_fondu = (float) $expedition->fondation->carrat_fondu;

                // 💎 Pureté locale = ((poids * carat) / 24) / carrat_moyen
             $purete_local    = ($poids_fondu * $carrat_fondu) / 24;
               
                $details[] = [
                    'id_expedition' => $expedition->id,
                    'poids_fondu'   => round($poids_fondu, 2),
                    'carrat_fondu'  => round($carrat_fondu, 2),
                    'purete_local'  => round($purete_local, 2),
                ];
            }
        }

        // === Étape 3 : Pureté totale globale ===
        $pureteTotale = ($poidsTotal * $carratMoyen) / 24;
        $pureteTotale = round($pureteTotale, 3);

        // ✅ Résultat final
        return [
            'poids_total'   => $poidsTotal,
            'carrat_moyen'  => $carratMoyen,
            'purete_totale' => $pureteTotale,
            'details'       => $details,
        ];
    }

}

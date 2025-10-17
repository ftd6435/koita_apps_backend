<?php
namespace App\Modules\Fondation\Services;

use App\Modules\Fondation\Models\Fondation;
use App\Modules\Fondation\Models\InitFondation;
use App\Modules\Fondation\Resources\FondationResource;
use App\Modules\Fondation\Resources\FondationResource1;
use App\Modules\Purchase\Models\Achat;
use App\Modules\Purchase\Models\Barre;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FondationService
{
    /**
     * 🔹 Créer une nouvelle fondation (avec gestion des statuts barres).
     */

    public function store(array $payload)
    {
        DB::beginTransaction();

        try {
            // 🔹 On accepte un seul objet ou un tableau d’objets
            $fondations = isset($payload[0]) ? $payload : [$payload];
            $resultats  = [];

            // =========================================
            // 🔹 1️⃣ Création ou récupération de InitFondation
            // =========================================
            $reference = $fondations[0]['reference'] ?? null;

            if (! empty($reference)) {
                // Si la référence est fournie → on crée l’init avec cette référence
                $initFondation = InitFondation::create([
                    'reference'  => $reference,
                    'created_by' => Auth::id(),
                ]);
            } else {
                // Sinon → on génère automatiquement une référence unique
                $initFondation = InitFondation::create([
                    'created_by' => Auth::id(),
                ]);
            }

            // =========================================
            // 🔹 2️⃣ Parcours et création des fondations
            // =========================================
            foreach ($fondations as $data) {

                // 🔹 Normaliser les IDs des barres
                $ids = collect($data['ids_barres'])->map(fn($id) => (int) $id)->toArray();

                // 🔹 Récupérer les achats liés à ces barres
                $achatsIds = Barre::whereIn('id', $ids)
                    ->pluck('achat_id')
                    ->unique()
                    ->filter()
                    ->toArray();

               

                // 🔹 Mettre à jour les statuts des barres
                if (count($ids) === 1) {
                    Barre::where('id', $ids[0])->update(['status' => 'fondue']);
                } else {
                    Barre::whereIn('id', $ids)->update(['status' => 'fusionner']);
                }

                // 🔹 Mettre à jour les achats associés
                Achat::whereIn('id', $achatsIds)->update([
                    'etat'   => 'fondue',
                    'status' => 'terminer',
                ]);

                // =========================================
                // 🔹 3️⃣ Création de la fondation liée à l’init
                // =========================================
                $fondation = Fondation::create([
                    'ids_barres'        => implode(',', $ids),
                    'poids_fondu'       => $data['poids_fondu'] ?? 0,
                    'carrat_fondu'      => $data['carrat_fondu'] ?? 0,
                    'poids_dubai'       => $data['poids_dubai'] ?? 0,
                    'carrat_dubai'      => $data['carrat_dubai'] ?? 0,
                    'is_fixed'          => $data['is_fixed'] ?? false,
                    'id_init_fondation' => $initFondation->id, // 💥 Lien vers l’init
                    'created_by'        => Auth::id(),
                ]);

                $resultats[] = new FondationResource($fondation);
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Fondation(s) créée(s) avec succès.',
                'data'    => count($resultats) === 1 ? $resultats[0] : $resultats,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de la fondation.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Récupérer toutes les fondations.
     */
    public function getAll()
    {
        try {
            $fondations = Fondation::orderByDesc('id')->get();

            return response()->json([
                'status'  => 200,
                'message' => 'Liste des fondations récupérée avec succès.',
                'data'    => FondationResource::collection($fondations),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des fondations.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function getAll1()
{
    try {
        // ✅ Récupérer uniquement les fondations non fixées
        $fondations = Fondation::where('is_fixed', false)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status'  => 200,
            'message' => 'Liste des fondations non fixées récupérée avec succès.',
            'data'    => FondationResource1::collection($fondations),
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status'  => 500,
            'message' => 'Erreur lors de la récupération des fondations.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    /**
     * 🔹 Récupérer une seule fondation.
     */
    public function getOne(int $id)
    {
        try {
            $fondation = Fondation::findOrFail($id);

            return response()->json([
                'status'  => 200,
                'message' => 'Fondation récupérée avec succès.',
                'data'    => new FondationResource($fondation),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'Fondation non trouvée.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Supprimer une fondation (soft delete).
     */
    public function delete(int $id)
    {
        try {
            $fondation = Fondation::findOrFail($id);
            $fondation->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Fondation supprimée avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la fondation.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
}

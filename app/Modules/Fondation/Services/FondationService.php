<?php
namespace App\Modules\Fondation\Services;

use App\Modules\Fondation\Models\Fondation;
use App\Modules\Fondation\Resources\FondationResource;
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
    // public function store(array $data)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $ids = $data['ids_barres'];
    //         $barres = Barre::whereIn('id', $ids)->get();

    //         if ($barres->isEmpty()) {
    //             return response()->json([
    //                 'status'  => 404,
    //                 'message' => 'Aucune barre trouvée pour la fondation.',
    //             ]);
    //         }

    //         // 🔹 Mise à jour du statut des barres
    //         if (count($ids) === 1) {
    //             // Une seule barre → fondue
    //             Barre::where('id', $ids[0])->update(['status' => 'fondu']);
    //         } else {
    //             // Plusieurs barres → fusionner
    //             Barre::whereIn('id', $ids)->update(['status' => 'fusionner']);
    //         }

    //         // 🔹 Création de la fondation
    //         $data['created_by'] = Auth::id();
    //         $data['ids_barres'] = implode(',', $ids); // conversion en chaîne

    //         $fondation = Fondation::create($data);

    //         DB::commit();

    //         return response()->json([
    //             'status'  => 200,
    //             'message' => 'Fondation créée avec succès.',
    //             'data'    => new FondationResource($fondation),
    //         ]);
    //     } catch (Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status'  => 500,
    //             'message' => 'Erreur lors de la création de la fondation.',
    //             'error'   => $e->getMessage(),
    //         ]);
    //     }
    // }
    // public function store(array $payload)
    // {
    //     DB::beginTransaction();

    //     try {
    //         // On accepte soit un seul objet, soit un tableau d’objets
    //         $fondations = isset($payload[0]) ? $payload : [$payload];

    //         $resultats = [];

    //         foreach ($fondations as $data) {
    //             // Normaliser les IDs des barres
    //             $ids = collect($data['ids_barres'])->map(fn($id) => (int) $id)->toArray();

    //             // Mettre à jour les statuts des barres selon leur nombre
    //             if (count($ids) === 1) {
    //                 Barre::where('id', $ids[0])->update(['status' => 'fondue']);
    //             } else {
    //                 Barre::whereIn('id', $ids)->update(['status' => 'fusionner']);
    //             }

    //             // Création de la fondation
    //             $fondation = Fondation::create([
    //                 'ids_barres'   => implode(',', $ids),
    //                 'poid_fondu'   => $data['poid_fondu'],
    //                 'carat_moyen'  => $data['carat_moyen'],
    //                 'poids_dubai'  => $data['poids_dubai'] ?? 0,
    //                 'carrat_dubai' => $data['carrat_dubai'] ?? 0,
    //                 'is_fixed'     => $data['is_fixed'] ?? false,
    //                 'created_by'   => Auth::id(),
    //             ]);

    //             $resultats[] = new FondationResource($fondation);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status'  => 200,
    //             'message' => 'Fondation(s) créée(s) avec succès.',
    //             'data'    => count($resultats) === 1 ? $resultats[0] : $resultats,
    //         ]);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'status'  => 500,
    //             'message' => 'Erreur lors de la création de la fondation.',
    //             'error'   => $e->getMessage(),
    //         ]);
    //     }
    // }
    public function store(array $payload)
{
    DB::beginTransaction();

    try {
        // 🔹 On accepte un seul objet ou un tableau d’objets
        $fondations = isset($payload[0]) ? $payload : [$payload];
        $resultats  = [];

        foreach ($fondations as $data) {
            // 🔹 Vérification : ids_barres doit être présent
            if (empty($data['ids_barres']) || !is_array($data['ids_barres'])) {
                DB::rollBack();
                return response()->json([
                    'status'  => 422,
                    'message' => 'Erreur de validation.',
                    'errors'  => ['ids_barres' => ['Le champ ids_barres est requis et doit être un tableau.']],
                ], 422);
            }

            // 🔹 Normaliser les IDs des barres
            $ids = collect($data['ids_barres'])->map(fn($id) => (int) $id)->toArray();

            // 🔹 Récupérer les achats liés à ces barres
            $achatsIds = Barre::whereIn('id', $ids)
                ->pluck('achat_id')
                ->unique()
                ->filter()
                ->toArray();

            if (empty($achatsIds)) {
                DB::rollBack();
                return response()->json([
                    'status'  => 404,
                    'message' => 'Aucun achat associé aux barres fournies.',
                ], 404);
            }

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

            // 🔹 Création de la fondation
            $fondation = Fondation::create([
                'ids_barres'   => implode(',', $ids),
                'poids_fondu'   => $data['poids_fondu'] ?? 0,
                'carrat_fondu'  => $data['carrat_fondu'] ?? 0,
                'poids_dubai'  => $data['poids_dubai'] ?? 0,
                'carrat_dubai' => $data['carrat_dubai'] ?? 0,
                'is_fixed'     => $data['is_fixed'] ?? false,
                'created_by'   => Auth::id(),
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

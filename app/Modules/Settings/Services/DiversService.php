<?php
namespace App\Modules\Settings\Services;

use App\Modules\Comptabilite\Models\OperationDivers;
use App\Modules\Settings\Models\Divers;
use App\Modules\Settings\Resources\DiversResource;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DiversService
{
    /**
     * 🔹 Créer un nouvel enregistrement Divers
     */
    public function store(array $data)
    {
        try {
            $data['created_by'] = Auth::id();
            $divers             = Divers::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Enregistrement Divers créé avec succès.',
                'data'    => new DiversResource(
                    $divers->load(['createur', 'modificateur', 'operationsDivers'])
                ),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de l’enregistrement Divers.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Mettre à jour un enregistrement Divers
     */
    public function update(int $id, array $data)
    {
        try {
            $divers             = Divers::findOrFail($id);
            $data['updated_by'] = Auth::id();
            $divers->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Enregistrement Divers mis à jour avec succès.',
                'data'    => new DiversResource(
                    $divers->load(['createur', 'modificateur', 'operationsDivers'])
                ),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour de l’enregistrement Divers.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Supprimer un enregistrement Divers
     */
    public function delete(int $id)
    {
        try {
            $divers = Divers::findOrFail($id);
            $divers->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Enregistrement Divers supprimé avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de l’enregistrement Divers.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Récupérer tous les enregistrements Divers
     */
    public function getAll()
    {
        try {
            $divers = Divers::with(['createur', 'modificateur', 'operationsDivers'])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status'  => 200,
                'message' => 'Liste des enregistrements Divers récupérée avec succès.',
                'data'    => DiversResource::collection($divers),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des enregistrements Divers.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Récupérer un enregistrement Divers spécifique
     */
    public function getOne(int $id)
    {
        try {
            $divers = Divers::with(['createur', 'modificateur', 'operationsDivers'])
                ->findOrFail($id);

            return response()->json([
                'status'  => 200,
                'message' => 'Détails du Divers récupérés avec succès.',
                'data'    => new DiversResource($divers),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'Enregistrement Divers introuvable.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    // public function calculerSoldeDivers(int $id_divers, int $cacheMinutes = 5): array
    // {
    //     return Cache::remember("solde_divers_{$id_divers}", now()->addMinutes($cacheMinutes), function () use ($id_divers) {

    //         $operations = OperationDivers::with(['typeOperation', 'devise'])
    //             ->where('id_divers', $id_divers)
    //             ->get();

    //         $soldes = [];

    //         foreach ($operations as $op) {
    //             $devise  = strtoupper($op->devise?->symbole ?? 'GNF');
    //             $nature  = $op->typeOperation?->nature ?? 1;
    //             $montant = (float) $op->montant;
    //             $taux    = (float) ($op->taux_jour ?? 1);

    //             // ✅ Si devise est GNF → pas de conversion, on ajoute normalement
    //             if ($devise === 'GNF') {
    //                 $soldes['gnf'] = ($soldes['gnf'] ?? 0)
    //                      + ($nature == 1 ? $montant : -$montant);
    //                 continue;
    //             }

    //             // ✅ Si taux_jour ≠ 1 → conversion en GNF uniquement ✅
    //             if ($taux != 1) {
    //                 $montantConverti = $montant * $taux;
    //                 $soldes['gnf']   = ($soldes['gnf'] ?? 0)
    //                      + ($nature == 1 ? $montantConverti : -$montantConverti);
    //             } else {
    //                 // ✅ Sinon, solde dans la devise d'origine (USD ou autre)
    //                 $soldes[strtolower($devise)] = ($soldes[strtolower($devise)] ?? 0)
    //                      + ($nature == 1 ? $montant : -$montant);
    //             }
    //         }

    //         return collect($soldes)
    //             ->map(fn($s) => round($s, 2))
    //             ->toArray();
    //     });
    // }

    public function calculerSoldeDivers(int $id_divers, int $cacheMinutes = 5): array
    {
        return Cache::remember("solde_divers_{$id_divers}", now()->addMinutes($cacheMinutes), function () use ($id_divers) {

            $operations = OperationDivers::with(['typeOperation', 'devise'])
                ->where('id_divers', $id_divers)
                ->get();

            // ✅ Variables flux à ajouter
            $entrees_usd = 0;
            $sorties_usd = 0;
            $entrees_gnf = 0;
            $sorties_gnf = 0;

            $soldes = []; // ✅ Garde l’existant

            foreach ($operations as $op) {
                $devise  = strtoupper($op->devise?->symbole ?? 'GNF');
                $nature  = $op->typeOperation?->nature ?? 1;
                $montant = (float) $op->montant;
                $taux    = (float) ($op->taux_jour ?? 1);

                // ✅ Si devise est GNF → flux GNF
                if ($devise === 'GNF') {
                    if ($nature == 1) {
                        $entrees_gnf += $montant;
                    } else {
                        $sorties_gnf += $montant;
                    }

                    $soldes['gnf'] = ($soldes['gnf'] ?? 0)
                         + ($nature == 1 ? $montant : -$montant);

                    continue;
                }

                // ✅ Si devise ≠ GNF → gestion USD
                if ($taux != 1) {
                    // ✅ Conversion → flux GNF
                    $montantConverti = $montant * $taux;

                    if ($nature == 1) {
                        $entrees_gnf += $montantConverti;
                    } else {
                        $sorties_gnf += $montantConverti;
                    }

                    $soldes['gnf'] = ($soldes['gnf'] ?? 0)
                         + ($nature == 1 ? $montantConverti : -$montantConverti);
                } else {
                    // ✅ Flux USD normal
                    if ($nature == 1) {
                        $entrees_usd += $montant;
                    } else {
                        $sorties_usd += $montant;
                    }

                    $soldes[strtolower($devise)] = ($soldes[strtolower($devise)] ?? 0)
                         + ($nature == 1 ? $montant : -$montant);
                }
            }

            // ✅ Retour complet en incluant les flux
            return [
                'usd'         => round($soldes['usd'] ?? 0, 2),
                'gnf'         => round($soldes['gnf'] ?? 0, 2),

                // ✅ Ajout demandé : flux
                'entrees_usd' => round($entrees_usd, 2),
                'sorties_usd' => round($sorties_usd, 2),
                'entrees_gnf' => round($entrees_gnf, 2),
                'sorties_gnf' => round($sorties_gnf, 2),
            ];
        });
    }

    public function getReleveDivers(int $id_divers): array
    {
        $operations = OperationDivers::with(['typeOperation', 'devise'])
            ->where('id_divers', $id_divers)
            ->orderByDesc('date_operation')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($op) {
                $nature = $op->typeOperation?->nature; // 1 = entrée, 0 = sortie

                return [
                    'date'        => $op->date_operation
                        ? (is_string($op->date_operation)
                            ? $op->date_operation
                            : $op->date_operation->format('Y-m-d H:i:s'))
                        : $op->created_at?->format('Y-m-d H:i:s'),

                    'reference'   => $op->reference ?? '',
                    'libelle'     => $op->typeOperation?->libelle ?? 'Opération Divers',
                    'devise'      => $op->devise?->symbole ?? '',
                    'commentaire' => $op->commentaire ?? '',
                    'debit'       => $nature == 0 ? (float) $op->montant : 0,
                    'credit'      => $nature == 1 ? (float) $op->montant : 0,
                ];
            });

        $soldeUSD = 0;
        $soldeGNF = 0;

        $operations = $operations->reverse()->map(function ($op) use (&$soldeUSD, &$soldeGNF) {
            if ($op['devise'] === 'USD') {
                $soldeUSD += $op['credit'] - $op['debit'];
                $op['solde_apres'] = round($soldeUSD, 2);
            } elseif ($op['devise'] === 'GNF') {
                $soldeGNF += $op['credit'] - $op['debit'];
                $op['solde_apres'] = round($soldeGNF, 2);
            } else {
                $op['solde_apres'] = null;
            }

            return $op;
        })->reverse()->values();

        return $operations->toArray();
    }

}

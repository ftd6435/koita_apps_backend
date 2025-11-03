<?php
namespace App\Modules\Settings\Services;

use App\Modules\Comptabilite\Models\OperationDivers;
use App\Modules\Settings\Models\Devise;
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

            // 🔹 Récupérer toutes les devises actives
            $devises = Devise::select('id', 'symbole')->get();

            $soldes = [];
            $flux   = [];

            $operations = OperationDivers::with(['typeOperation', 'devise'])
                ->where('id_divers', $id_divers)
                ->get();

            // 🔸 Initialisation dynamique pour chaque devise
            foreach ($devises as $devise) {
                $symbole = strtolower($devise->symbole);

                $soldes[$symbole] = 0;
                $flux[$symbole]   = [
                    'entrees' => 0,
                    'sorties' => 0,
                ];
            }

            // 🔹 Parcours de toutes les opérations
            foreach ($operations as $op) {
                $devise  = strtolower($op->devise?->symbole ?? 'gnf');
                $nature  = $op->typeOperation?->nature ?? 1; // 1 = entrée, 0 = sortie
                $montant = (float) $op->montant;

                // 🔸 Si la devise n’existe pas encore (cas de devise ajoutée en cours)
                if (! isset($soldes[$devise])) {
                    $soldes[$devise] = 0;
                    $flux[$devise]   = [
                        'entrees' => 0,
                        'sorties' => 0,
                    ];
                }

                // 🔸 Traitement selon la nature
                if ($nature == 1) {
                    $flux[$devise]['entrees'] += $montant;
                    $soldes[$devise] += $montant;
                } else {
                    $flux[$devise]['sorties'] += $montant;
                    $soldes[$devise] -= $montant;
                }
            }

            // 🔹 Arrondir toutes les valeurs
            foreach ($soldes as $symbole => &$val) {
                $val = round($val, 2);
            }

            foreach ($flux as $symbole => &$item) {
                $item['entrees'] = round($item['entrees'], 2);
                $item['sorties'] = round($item['sorties'], 2);
            }

            // 🔹 Structure finale propre
            return [
                'soldes' => $soldes,
                'flux'   => $flux,
            ];
        });
    }

    // public function getReleveDivers(int $id_divers): array
    // {
    //     $operations = OperationDivers::with(['typeOperation', 'devise'])
    //         ->where('id_divers', $id_divers)
    //         ->orderByDesc('date_operation')
    //         ->orderByDesc('created_at')
    //         ->get()
    //         ->map(function ($op) {
    //             $nature = $op->typeOperation?->nature; // 1 = entrée, 0 = sortie

    //             return [
    //                 'date'        => $op->date_operation
    //                     ? (is_string($op->date_operation)
    //                         ? $op->date_operation
    //                         : $op->date_operation->format('Y-m-d H:i:s'))
    //                     : $op->created_at?->format('Y-m-d H:i:s'),

    //                 'reference'   => $op->reference ?? '',
    //                 'libelle'     => $op->typeOperation?->libelle ?? 'Opération Divers',
    //                 'devise'      => $op->devise?->symbole ?? '',
    //                 'commentaire' => $op->commentaire ?? '',
    //                 'debit'       => $nature == 0 ? (float) $op->montant : 0,
    //                 'credit'      => $nature == 1 ? (float) $op->montant : 0,
    //             ];
    //         });

    //     $soldeUSD = 0;
    //     $soldeGNF = 0;

    //     $operations = $operations->reverse()->map(function ($op) use (&$soldeUSD, &$soldeGNF) {
    //         if ($op['devise'] === 'USD') {
    //             $soldeUSD += $op['credit'] - $op['debit'];
    //             $op['solde_apres'] = round($soldeUSD, 2);
    //         } elseif ($op['devise'] === 'GNF') {
    //             $soldeGNF += $op['credit'] - $op['debit'];
    //             $op['solde_apres'] = round($soldeGNF, 2);
    //         } else {
    //             $op['solde_apres'] = null;
    //         }

    //         return $op;
    //     })->reverse()->values();

    //     return $operations->toArray();
    // }

    // public function getReleveDivers(int $id_divers): array
    // {
    //     $operations = OperationDivers::with(['typeOperation', 'devise'])
    //         ->where('id_divers', $id_divers)
    //         ->orderByDesc('date_operation')
    //         ->orderByDesc('created_at')
    //         ->get()
    //         ->map(function ($op) {
    //             $nature = $op->typeOperation?->nature; // 1 = entrée, 0 = sortie

    //             return [
    //                 'date'        => $op->date_operation
    //                     ? (is_string($op->date_operation)
    //                         ? $op->date_operation
    //                         : $op->date_operation->format('Y-m-d H:i:s'))
    //                     : $op->created_at?->format('Y-m-d H:i:s'),

    //                 'reference'   => $op->reference ?? '',
    //                 'libelle'     => $op->typeOperation?->libelle ?? 'Opération Divers',
    //                 'devise'      => $op->devise?->symbole ?? '',
    //                 'commentaire' => $op->commentaire ?? '',
    //                 'debit'       => $nature == 0 ? (float) $op->montant : 0,
    //                 'credit'      => $nature == 1 ? (float) $op->montant : 0,
    //             ];
    //         });

    //     $soldeUSD = 0;
    //     $soldeGNF = 0;
    //     $usdList  = [];
    //     $gnfList  = [];

    //     // ✅ Calcul du solde progressif sans casser l’ordre d’affichage
    //     $operations = $operations->reverse()->map(function ($op) use (&$soldeUSD, &$soldeGNF) {
    //         if ($op['devise'] === 'USD') {
    //             $soldeUSD += $op['credit'] - $op['debit'];
    //             $op['solde_apres'] = round($soldeUSD, 2);
    //         } elseif ($op['devise'] === 'GNF') {
    //             $soldeGNF += $op['credit'] - $op['debit'];
    //             $op['solde_apres'] = round($soldeGNF, 2);
    //         } else {
    //             $op['solde_apres'] = null;
    //         }

    //         return $op;
    //     })->reverse()->values();

    //     // ✅ Séparation en deux devises
    //     foreach ($operations as $op) {
    //         if ($op['devise'] === 'USD') {
    //             $usdList[] = $op;
    //         } elseif ($op['devise'] === 'GNF') {
    //             $gnfList[] = $op;
    //         }
    //     }

    //     return [
    //         'usd' => $usdList,
    //         'gnf' => $gnfList,
    //     ];
    // }

    public function getReleveDivers(int $id_divers): array
    {
        // 🔹 Récupérer toutes les devises actives
        $devises = Devise::pluck('symbole')->map(fn($s) => strtolower($s));

        // 🔹 1. Récupération des opérations
        $operations = OperationDivers::with(['typeOperation', 'devise'])
            ->where('id_divers', $id_divers)
            ->orderBy('date_operation')
            ->orderBy('created_at')
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
                    'devise'      => strtolower($op->devise?->symbole ?? ''),
                    'commentaire' => $op->commentaire ?? '',
                    'debit'       => $nature == 0 ? (float) $op->montant : 0,
                    'credit'      => $nature == 1 ? (float) $op->montant : 0,
                ];
            });

        // 🔹 2. Initialisation dynamique des soldes
        $soldes  = [];
        $releves = [];

        foreach ($devises as $symbole) {
            $soldes[$symbole]  = 0;
            $releves[$symbole] = [];
        }

        // 🔹 3. Calcul des soldes progressifs
        foreach ($operations as $op) {
            $symbole = $op['devise'];

            if (! isset($soldes[$symbole])) {
                $soldes[$symbole]  = 0;
                $releves[$symbole] = [];
            }

            $soldes[$symbole] += $op['credit'] - $op['debit'];
            $op['solde_apres'] = round($soldes[$symbole], 2);

            $releves[$symbole][] = $op;
        }

        // 🔹 4. Inversion des listes (du plus récent au plus ancien)
        foreach ($releves as $symbole => &$list) {
            $list = array_reverse($list);
        }

        return $releves;
    }

}

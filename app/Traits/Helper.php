<?php

namespace App\Traits;

use App\Modules\Administration\Models\Fournisseur;
use App\Modules\Comptabilite\Models\Caisse;
use App\Modules\Comptabilite\Models\Compte;
use App\Modules\Comptabilite\Models\FournisseurOperation;
use App\Modules\Comptabilite\Models\OperationClient;
use App\Modules\Comptabilite\Models\OperationDivers;
use App\Modules\Fixing\Models\Fixing;
use App\Modules\Fixing\Models\FixingBarre;
use App\Modules\Fondation\Models\Fondation;
use App\Modules\Purchase\Models\Barre;
use App\Modules\Settings\Models\Devise;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait Helper
{
    public function carratMoyenne($achat_id)
    {
        $barres = Barre::where('achat_id', $achat_id)->get();

        // $multplication = 0;

        foreach ($barres as $barre) {
            // $multplication += $barre->poid_pure * $barre->carrat_pure;
            $sum_pureter = $this->pureter($barre->poid_pure, $barre->carrat_pure);
        }

        $poids = $barres->sum('poid_pure');

        if($poids == 0){
            return 0;
        }

        // $moyenne = $somme > 0 ? ($multplication / $somme) : 0;
        $moyenne = ($sum_pureter / $poids) * 24;

        return (float) number_format($moyenne, 2);
    }

    public function pureterMoyenne($achat)
    {
        $barres = Barre::where('achat_id', $achat)->get();
        $somme_pureter = 0;

        foreach($barres as $barre){
            $somme_pureter += $this->pureter($barre->poid_pure, $barre->carrat_pure);
        }

        return (float) number_format($somme_pureter, 2);
    }

    public function poidsFixing($fixing_id)
    {
        $fixing_barres = FixingBarre::where('fixing_id', $fixing_id)->get();

        if (!$fixing_barres) {
            return 0;
        }

        $poids_total = 0;

        foreach ($fixing_barres as $fixing_barre) {
            $barre = Barre::find($fixing_barre->barre_id);

            if ($barre->status == 'non fondue' || $barre->status == 'fusionner') {
                $poids_total += $barre->poid_pure;
            } else {
                $barre_fondue = Fondation::where('ids_barres', $fixing_barre->barre_id)->first();

                if ($barre_fondue && $barre_fondue->statut == 'corriger') {
                    $poids_total += $barre_fondue->poids_dubai;
                } else {
                    $poids_total += $barre->poid_pure;
                }
            }
        }

        return $poids_total;
    }

    public function carratFixing($fixing_id)
    {
        $multplication = $this->poidsTimeCarrat($fixing_id);
        $poids_total = $this->poidsFixing($fixing_id);

        $result = $poids_total > 0 ? ($multplication / $poids_total) : 0;

        return $result;
    }

    protected function poidsTimeCarrat($fixing_id)
    {
        $fixing_barres = FixingBarre::where('fixing_id', $fixing_id)->get();

        if (!$fixing_barres) {
            return 0;
        }

        $multplication = 0;

        foreach ($fixing_barres as $fixing_barre) {
            $barre = Barre::find($fixing_barre->barre_id);

            if ($barre->status == 'non fondue' || $barre->status == 'fusionner') {
                $multplication += $barre->poid_pure * $barre->carrat_pure;
            } else {
                $barre_fondue = Fondation::where('ids_barres', $fixing_barre->barre_id)->first();

                if ($barre_fondue && $barre_fondue->statut == 'corriger') {
                    $multplication += $barre_fondue->poids_dubai * $barre_fondue->poids_dubai;
                } else {
                    $multplication += $barre->poid_pure * $barre->carrat_pure;
                }
            }
        }

        return $multplication;
    }

    public function barreFondue($id)
    {
        $barre_fondue = Fondation::where('ids_barres', $id)->first();

        if (!$barre_fondue) {
            return null;
        }

        $barre = [
            'id' => $barre_fondue->id,
            'poid_fondu' => $barre_fondue->poids_fondu,
            'carrat_fondu' => $barre_fondue->carrat_fondu,
            'poids_dubai' => $barre_fondue->poids_dubai,
            'carrat_dubai' => $barre_fondue->carrat_dubai,
        ];

        return $barre;
    }

    /**
     * Cette methode retourne le montant correspondant a chaque barre.
     */
    public function montantBarre($id, $unit_price)
    {
        // Retrieve barre safely
        $barre = Barre::find($id);
        if (!$barre) {
            return 0; // or throw exception if needed
        }

        // Normalize all numeric inputs
        $unit_price = ($unit_price ?? 0);
        $densite = ($barre->densite ?? 0);
        $poid_pure = ($barre->poid_pure ?? 0);
        $carrat_pure = ($barre->carrat_pure ?? 0);

        $montant = 0;

        if ($barre->status === 'fondue') {
            $barre_fondue = Fondation::where('ids_barres', $barre->id)->first();

            if ($barre_fondue) {
                $poids_dubai = ($barre_fondue->poids_dubai ?? 0);
                $carrat_dubai = ($barre_fondue->carrat_dubai ?? 0);

                if ($barre_fondue->statut === 'corriger') {
                    $montant = ($densite > 0 ? $unit_price / $densite : 0) * $poids_dubai * $carrat_dubai;
                } else {
                    $montant = ($densite > 0 ? $unit_price / $densite : 0) * $poid_pure * $carrat_pure;
                }
            }
        } else {
            $montant = ($densite > 0 ? $unit_price / $densite : 0) * $poid_pure * $carrat_pure;
        }

        return $montant;
    }

    /**
     * Cette methode retourne le montant total de chaque fixing.
     */
    public function montantFixing($fixing_id)
    {
        $fixing = Fixing::find($fixing_id);
        if (!$fixing) {
            return 0;
        }

        // $fixing_barres = FixingBarre::where('fixing_id', $fixing->id)->get();
        $montant_total = 0;
        $unit_price = ($fixing->unit_price ?? 0);

        // foreach ($fixing_barres as $barre) {
        //     $montant_total +=  $this->montantBarre($barre->barre_id, $unit_price);
        // }

        $montant_total = ($unit_price / 22) * $this->poidsFixing($fixing->id) * $this->carratFixing($fixing->id);

        return $montant_total;
    }

    /**
     * Cette methode retourne un tableau de grand total montant d'un fournisseur pour chaque devise.
     */
    public function montantTotalFixing($fournisseurId)
    {
        $fixings = Fixing::with('devise')
            ->where('fournisseur_id', $fournisseurId)
            ->get();

        if ($fixings->isEmpty()) {
            return [];
        }

        $totals = [];

        foreach ($fixings as $fixing) {
            $montant = $this->montantFixing($fixing->id);

            $symbole = $fixing->devise->symbole ?? 'N/A';

            if (!isset($totals[$symbole])) {
                $totals[$symbole] = 0;
            }

            $totals[$symbole] += $montant;
        }

        $result = [];
        foreach ($totals as $symbole => $montant) {
            $result[] = [
                'symbole' => $symbole,
                'montant' => $montant,
            ];
        }

        return $result;
    }

    /**
     * Cette methode retourne la pureter de l'or.
     */
    public function pureter($poid, $carrat)
    {
        $result = ($poid * $carrat) / 24;

        return $result;
    }

    /**
     * Cette method est pour retourner le solde d'un fournisseur donner par rapport a ces opérations.
     */
    public function soldeFournisseurOperations($fournisseurId)
    {
        return FournisseurOperation::where('fournisseur_id', $fournisseurId)
            ->join('type_operations as to', 'fournisseur_operations.type_operation_id', '=', 'to.id')
            ->join('devises as d', 'fournisseur_operations.devise_id', '=', 'd.id')
            ->select(
                'd.symbole',
                DB::raw('SUM(CASE WHEN to.nature = 1 THEN fournisseur_operations.montant ELSE -fournisseur_operations.montant END) as montant')
            )
            ->groupBy('d.id', 'd.symbole')
            ->get()
            ->toArray();
    }

    /**
     * Cette method retourne le solde global du fournisseur par devise.
     */
    public function soldeGlobalFournisseur($fournisseurId)
    {
        // Get both datasets
        $operations = $this->soldeFournisseurOperations($fournisseurId);
        $fixings = $this->montantTotalFixing($fournisseurId);

        // Get all available currencies from the database
        $allCurrencies = Devise::pluck('symbole')->toArray();

        // Convert to associative arrays for easy merging
        $totals = [];

        // Initialize all currencies with 0
        foreach ($allCurrencies as $symbole) {
            $totals[$symbole] = 0;
        }

        // Add operations balances
        foreach ($operations as $op) {
            $symbole = $op['symbole'];
            $montant = $op['montant'];
            $totals[$symbole] = ($totals[$symbole] ?? 0) + $montant;
        }

        // Add fixing balances
        foreach ($fixings as $fix) {
            $symbole = $fix['symbole'];
            $montant = $fix['montant'];
            $totals[$symbole] = ($totals[$symbole] ?? 0) + $montant;
        }

        // Convert back to array format
        $result = [];
        foreach ($totals as $symbole => $montant) {
            $result[] = [
                'symbole' => $symbole,
                'montant' => $montant,
            ];
        }

        return $result;
    }

    /**
     * Cette methode retourne le total entrees et sorties d'un fournisseur
     * Grouper par devise.
     */
    public function supplierBalancePerCurrency($fournisseurId)
    {
        // Initialize result array with all available currencies
        $devises = Devise::all();
        $result = [];

        foreach ($devises as $devise) {
            $symbole = strtolower($devise->symbole);
            $result["entrees_{$symbole}"] = 0;
            $result["sorties_{$symbole}"] = 0;
        }

        // Operations
        $operations = FournisseurOperation::with(['typeOperation', 'devise'])
            ->where('fournisseur_id', $fournisseurId)
            ->get();

        foreach ($operations as $operation) {
            $symbole = strtolower($operation->devise->symbole);
            $nature = $operation->typeOperation->nature;
            $key = ($nature == 1 ? 'entrees_' : 'sorties_').$symbole;
            $result[$key] += $operation->montant;
        }

        // Fixings
        $fixings = Fixing::with('devise')->where('fournisseur_id', $fournisseurId)->get();

        foreach ($fixings as $fixing) {
            $montant = $fixing->fixingBarres()->exists()
                ? $this->montantFixing($fixing->id)
                : ($fixing->unit_price / 22) * $fixing->poids_pro * $fixing->carrat_moyenne;

            $key = 'entrees_'.strtolower($fixing->devise->symbole);
            $result[$key] += $montant;
        }

        return $result;
    }

    /**
     * Cette method retourne toute l'historique des transaction d'un fournisseur.
     */
    // public function historiqueFournisseurComplet($fournisseurId)
    // {
    //     // ✅ Load fournisseur safely
    //     $fournisseur = Fournisseur::with([
    //         'operations.typeOperation',
    //         'operations.devise',
    //         'fixings.devise',
    //         'fixings.fixingBarres',
    //     ])->find($fournisseurId);

    //     // ✅ If supplier not found → return empty
    //     if (!$fournisseur) {
    //         return collect([]);
    //     }

    //     // ✅ 1. Transform operations (if any) - convert to base collection
    //     $operations = collect($fournisseur->operations?->map(function ($op) {
    //         $nature = $op->typeOperation?->nature ?? 0;
    //         $montant = (float) ($op->montant ?? 0);
    //         $dateOperation = $op->date_operation ? Carbon::parse($op->date_operation)->format('d-m-Y') : '';
    //         $mouvement = ($op->reference ?? '').': '.($op->commentaire ?? '').' le '.$dateOperation;

    //         return [
    //             'date' => $op->created_at,
    //             'mouvement' => $mouvement,
    //             'credit' => $nature == 1 ? $montant : 0,
    //             'debit' => $nature == 0 ? $montant : 0,
    //             'symbole' => $op->devise?->symbole ?? 'N/A',
    //         ];
    //     }) ?? []);

    //     // ✅ 2. Transform fixings (if any) - convert to base collection
    //     $fixings = collect($fournisseur->fixings?->map(function ($fixing) use ($fournisseur) {
    //         $devise = $fixing->devise;
    //         $symbole = $devise?->symbole ?? 'N/A';
    //         $hasBarres = $fixing->fixingBarres?->count() > 0;

    //         // Compute unit_price if USD
    //         $unit_price = (float) ($fixing->unit_price ?? 0);
    //         if (Str::upper($symbole) === 'USD') {
    //             $unit_price = ((float) ($fixing->bourse ?? 0) / 34) - (float) ($fixing->discount ?? 0);
    //         }

    //         // Compute montant
    //         if ($hasBarres) {
    //             $montant = (float) $this->montantFixing($fixing->id);
    //             $commentaire = "Fixing de {$fournisseur->name}";
    //         } else {
    //             $montant = ($unit_price / 22) * (float) ($fixing->poids_pro ?? 0) * (float) ($fixing->carrat_moyenne ?? 0);
    //             $commentaire = "Fixing provisoire par {$fournisseur->name}";
    //         }

    //         return [
    //             'date' => $fixing->created_at,
    //             'mouvement' => $commentaire,
    //             'credit' => round($montant, 2),
    //             'debit' => 0,
    //             'symbole' => $symbole,
    //         ];
    //     }) ?? []);

    //     // ✅ 3. Merge both collections (now both are base collections)
    //     $allTransactions = $operations->merge($fixings);

    //     // ✅ If no transactions at all → return empty
    //     if ($allTransactions->isEmpty()) {
    //         return collect([]);
    //     }

    //     // ✅ 4. Group by devise and compute running solde
    //     $historiques = $allTransactions
    //         ->sortBy('date')
    //         ->groupBy('symbole')
    //         ->map(function ($transactions) {
    //             $solde = 0;

    //             return $transactions->map(function ($t) use (&$solde) {
    //                 $solde += $t['credit'];
    //                 $solde -= $t['debit'];

    //                 return [
    //                     'date' => optional($t['date'])->format('d-m-Y H:i:s') ?? '',
    //                     'mouvement' => $t['mouvement'],
    //                     'credit' => $t['credit'],
    //                     'debit' => $t['debit'],
    //                     'solde' => round($solde, 2),
    //                 ];
    //             });
    //         });

    //     return $historiques;
    // }

    /**
     * Cette method retourne toute l'historique des transaction d'un fournisseur.
     */
    public function historiqueFournisseurComplet($fournisseurId)
    {
        // ✅ Load fournisseur safely
        $fournisseur = Fournisseur::with([
            'operations.typeOperation',
            'operations.devise',
            'fixings.devise',
            'fixings.fixingBarres.barre',
            'achats.barres',
        ])->find($fournisseurId);

        // ✅ If supplier not found → return empty
        if (!$fournisseur) {
            return collect([]);
        }

        // ✅ 1. Transform operations (if any) - convert to base collection
        $operations = collect($fournisseur->operations?->map(function ($op) {
            $nature = $op->typeOperation?->nature ?? 0;
            $montant = (float) ($op->montant ?? 0);
            $dateOperation = $op->date_operation ? Carbon::parse($op->date_operation)->format('d-m-Y') : '';
            $mouvement = ($op->reference ?? '').': '.($op->commentaire ?? '').' le '.$dateOperation;

            return [
                'date' => $op->created_at,
                'mouvement' => $mouvement,
                'credit' => $nature == 1 ? $montant : 0,
                'debit' => $nature == 0 ? $montant : 0,
                'symbole' => $op->devise?->symbole ?? 'N/A',
                'debit_or' => '-',
                'credit_or' => '-',
            ];
        }) ?? []);

        // ✅ 2. Transform fixings (if any) - convert to base collection
        $fixings = collect($fournisseur->fixings?->map(function ($fixing) use ($fournisseur) {
            $devise = $fixing->devise;
            $symbole = $devise?->symbole ?? 'N/A';
            $hasBarres = $fixing->fixingBarres?->count() > 0;

            // Compute debit_or (gold weight)
            $debit_or = 0;
            if ($hasBarres) {
                // Sum poid_pure from related barres through fixing_barres
                $debit_or = $fixing->fixingBarres->sum(function ($fixingBarre) {
                    return (float) ($fixingBarre->barre?->poid_pure ?? 0);
                });
            } else {
                // Use poids_pro if no barres
                $debit_or = (float) ($fixing->poids_pro ?? 0);
            }

            // Compute unit_price if USD
            $unit_price = (float) ($fixing->unit_price ?? 0);
            if (Str::upper($symbole) === 'USD') {
                $unit_price = ((float) ($fixing->bourse ?? 0) / 34) - (float) ($fixing->discount ?? 0);
            }

            // Compute montant
            if ($hasBarres) {
                $montant = (float) $this->montantFixing($fixing->id);
                $commentaire = "Fixing de {$fournisseur->name}";
            } else {
                $montant = ($unit_price / 22) * (float) ($fixing->poids_pro ?? 0) * (float) ($fixing->carrat_moyenne ?? 0);
                $commentaire = "Fixing provisoire par {$fournisseur->name}";
            }

            return [
                'date' => $fixing->created_at,
                'mouvement' => $commentaire,
                'credit' => round($montant, 2),
                'debit' => 0,
                'symbole' => $symbole,
                'debit_or' => round($debit_or, 3),
                'credit_or' => '-',
            ];
        }) ?? []);

        // ✅ 3. Transform achats (if any) - convert to base collection
        $achats = collect($fournisseur->achats?->map(function ($achat) use ($fournisseur) {
            // Sum poid_pure from related barres
            $credit_or = $achat->barres->sum(function ($barre) {
                return (float) ($barre->poid_pure ?? 0);
            });

            $commentaire = "Achat {$achat->reference} de {$fournisseur->name}";

            return [
                'date' => $achat->created_at,
                'mouvement' => $commentaire,
                'credit' => 0,
                'debit' => 0,
                'symbole' => 'OR', // Gold transactions use 'OR' as symbole
                'debit_or' => '-',
                'credit_or' => round($credit_or, 3),
            ];
        }) ?? []);

        // ✅ 4. Merge all collections (now all are base collections)
        $allTransactions = $operations->merge($fixings)->merge($achats);

        // ✅ If no transactions at all → return empty
        if ($allTransactions->isEmpty()) {
            return collect([]);
        }

        // ✅ 5. Sort all transactions by date
        $sortedTransactions = $allTransactions->sortBy('date')->values();

        // ✅ 6. Calculate running solde for monetary transactions and gold
        $solde = 0;
        $solde_or = 0;

        $historiques = $sortedTransactions->map(function ($t) use (&$solde, &$solde_or) {
            // Update monetary balance
            if ($t['credit'] > 0 || $t['debit'] > 0) {
                $solde += $t['credit'];
                $solde -= $t['debit'];
            }

            // Update gold balance
            if ($t['debit_or'] !== '-') {
                $solde_or -= (float) $t['debit_or'];
            }
            if ($t['credit_or'] !== '-') {
                $solde_or += (float) $t['credit_or'];
            }

            return [
                'date' => optional($t['date'])->format('d-m-Y H:i:s') ?? '',
                'mouvement' => $t['mouvement'],
                'credit' => $t['credit'] > 0 ? $t['credit'] : '-',
                'debit' => $t['debit'] > 0 ? $t['debit'] : '-',
                'solde' => round($solde, 2),
                'symbole' => $t['symbole'],
                'debit_or' => $t['debit_or'],
                'credit_or' => $t['credit_or'],
                'solde_or' => round($solde_or, 3),
            ];
        });

        return $historiques;
    }

    /**
     * Cette method retourn le poid total des barres non fixer.
     */
    public function poidsNonFixer($fournisseurId)
    {
        $total_non_fixer = Barre::whereHas('achat', function ($query) use ($fournisseurId) {
            $query->where('fournisseur_id', $fournisseurId);
        })
            ->where('is_fixed', false)
            ->sum('poid_pure');

        return $total_non_fixer ?? 0;
    }

    /**
     * Cette method retourne le carrat moyen des barres non fixer.
     */
    public function carratMoyenNonFixer($fournisseurId)
    {
        // Get all non-fixed barres for the fournisseur
        $barres = Barre::whereHas('achat', function ($query) use ($fournisseurId) {
            $query->where('fournisseur_id', $fournisseurId);
        })->where('is_fixed', false)->get(['poid_pure', 'carrat_pure']);

        // If no barres found, return 0
        if ($barres->isEmpty()) {
            return 0;
        }

        // Calculate sum of (poid_pure * carrat_pure)
        $sum_pureter = $barres->sum(function ($barre) {
            return $this->pureter($barre->poid_pure, $barre->carrat_pure);
        });

        // Calculate sum of poid_pure
        $sum_poid_pure = $barres->sum('poid_pure') ?? 0;

        // Avoid division by zero
        if ($sum_poid_pure == 0) {
            return 0;
        }

        // Calculate weighted average
        $carrat_moyen_non_fixer = ($sum_pureter / $sum_poid_pure) * 24;

        return (float) number_format($carrat_moyen_non_fixer, 2);
    }

    /**
     * Cette methode retourne tous les solde d'un compte dans les differents devise.
     */
    // public function getAccountBalance($compteId)
    // {
    //     // FournisseurOperation
    //     $fournisseurTotals = FournisseurOperation::with(['devise:id,symbole', 'typeOperation:id,nature'])
    //         ->where('compte_id', $compteId)
    //         ->get()
    //         ->filter(function ($operation) {
    //             return $operation->devise && $operation->typeOperation;
    //         })
    //         ->groupBy('devise.symbole')
    //         ->map(function ($group) {
    //             $deposits = $group->where('typeOperation.nature', 1)->sum('montant');
    //             $withdrawals = $group->where('typeOperation.nature', 0)->sum('montant');
    //             return $deposits - $withdrawals;
    //         });

    //     // OperationClient
    //     $clientTotals = OperationClient::with(['devise:id,symbole', 'typeOperation:id,nature'])
    //         ->where('id_compte', $compteId)
    //         ->get()
    //         ->filter(function ($operation) {
    //             return $operation->devise && $operation->typeOperation;
    //         })
    //         ->groupBy('devise.symbole')
    //         ->map(function ($group) {
    //             $deposits = $group->where('typeOperation.nature', 1)->sum('montant');
    //             $withdrawals = $group->where('typeOperation.nature', 0)->sum('montant');
    //             return $deposits - $withdrawals;
    //         });

    //     // OperationDivers
    //     $diversTotals = OperationDivers::with(['devise:id,symbole', 'typeOperation:id,nature'])
    //         ->where('id_compte', $compteId)
    //         ->get()
    //         ->filter(function ($operation) {
    //             return $operation->devise && $operation->typeOperation;
    //         })
    //         ->groupBy('devise.symbole')
    //         ->map(function ($group) {
    //             $deposits = $group->where('typeOperation.nature', 1)->sum('montant');
    //             $withdrawals = $group->where('typeOperation.nature', 0)->sum('montant');
    //             return $deposits - $withdrawals;
    //         });

    //     // CaisseOperation
    //     $caisseTotals = Caisse::with(['devise:id,symbole', 'typeOperation:id,nature'])
    //         ->where('id_compte', $compteId)
    //         ->get()
    //         ->filter(function ($operation) {
    //             return $operation->devise && $operation->typeOperation;
    //         })
    //         ->groupBy('devise.symbole')
    //         ->map(function ($group) {
    //             $deposits = $group->where('typeOperation.nature', 1)->sum('montant');
    //             $withdrawals = $group->where('typeOperation.nature', 0)->sum('montant');
    //             return $deposits - $withdrawals;
    //         });

    //     // Get initial balances from CompteDevise pivot table
    //     $initialBalances = CompteDevise::where('compte_id', $compteId)
    //         ->with('devise:id,symbole')
    //         ->get()
    //         ->filter(function ($compteDevise) {
    //             return $compteDevise->devise;
    //         })
    //         ->mapWithKeys(function ($compteDevise) {
    //             return [$compteDevise->devise->symbole => $compteDevise->solde_initial ?? 0];
    //         });

    //     // Merge all totals by currency
    //     $allCurrencies = collect([])
    //         ->merge($fournisseurTotals)
    //         ->merge($clientTotals)
    //         ->merge($diversTotals)
    //         ->merge($caisseTotals);

    //     // Combine with initial balances
    //     $combinedBalances = $initialBalances->map(function ($soldeInitial, $symbole) use ($allCurrencies) {
    //         $operationsTotal = $allCurrencies->get($symbole, 0);
    //         return $soldeInitial + $operationsTotal;
    //     });

    //     // Add any currencies from operations that don't have initial balances
    //     foreach ($allCurrencies as $symbole => $total) {
    //         if (!$combinedBalances->has($symbole)) {
    //             $combinedBalances->put($symbole, $total);
    //         }
    //     }

    //     // Handle empty results
    //     if ($combinedBalances->isEmpty()) {
    //         return [];
    //     }

    //     // Format as array
    //     $balances = $combinedBalances->map(function ($solde, $symbole) {
    //         return [
    //             'symbole' => $symbole,
    //             'solde' => round($solde, 2)
    //         ];
    //     })->values();

    //     return $balances->toArray();
    // }

    /**
     * Cette methode retourne un seul solde dans une devise d'un compte donné.
     */
    public function getAccountBalanceByDevise($compteId)
    {
        $compte = Compte::find($compteId);

        if (!$compte) {
            return 0;
        }

        // FournisseurOperation
        $fournisseurTotal = FournisseurOperation::with(['typeOperation:id,nature'])
            ->where('compte_id', $compte->id)
            ->where('devise_id', $compte->devise_id)
            ->get()
            ->filter(function ($operation) {
                return $operation->typeOperation;
            })
            ->reduce(function ($carry, $operation) {
                if ($operation->typeOperation->nature == 1) {
                    return $carry + $operation->montant; // Deposit
                } else {
                    return $carry - $operation->montant; // Withdrawal
                }
            }, 0) ?? 0;

        // OperationClient
        $clientTotal = OperationClient::with(['typeOperation:id,nature'])
            ->where('id_compte', $compte->id)
            ->where('id_devise', $compte->devise_id)
            ->get()
            ->filter(function ($operation) {
                return $operation->typeOperation;
            })
            ->reduce(function ($carry, $operation) {
                if ($operation->typeOperation->nature == 1) {
                    return $carry + $operation->montant;
                } else {
                    return $carry - $operation->montant;
                }
            }, 0) ?? 0;

        // OperationDivers
        $diversTotal = OperationDivers::with(['typeOperation:id,nature'])
            ->where('id_compte', $compte->id)
            ->where('id_devise', $compte->devise_id)
            ->get()
            ->filter(function ($operation) {
                return $operation->typeOperation;
            })
            ->reduce(function ($carry, $operation) {
                if ($operation->typeOperation->nature == 1) {
                    return $carry + $operation->montant;
                } else {
                    return $carry - $operation->montant;
                }
            }, 0) ?? 0;

        // CaisseOperation
        $caisseTotal = Caisse::with(['typeOperation:id,nature'])
            ->where('id_compte', $compte->id)
            ->where('id_devise', $compte->devise_id)
            ->get()
            ->filter(function ($operation) {
                return $operation->typeOperation;
            })
            ->reduce(function ($carry, $operation) {
                if ($operation->typeOperation->nature == 1) {
                    return $carry + $operation->montant;
                } else {
                    return $carry - $operation->montant;
                }
            }, 0) ?? 0;

        // Sum all totals
        $totalBalance = $fournisseurTotal + $clientTotal + $diversTotal + $caisseTotal + $compte->solde_initial;

        return round($totalBalance, 2);
    }

    public function arroundir(int $precision, float $valeur): float
    {
        return round($valeur, $precision);
    }
}

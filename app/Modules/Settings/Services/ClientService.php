<?php
namespace App\Modules\Settings\Services;

use App\Modules\Comptabilite\Models\OperationClient;
use App\Modules\Fixing\Models\FixingClient;
use App\Modules\Fixing\Models\InitLivraison;
use App\Modules\Fixing\Services\FixingClientService;
use App\Modules\Fondation\Models\Fondation;
use App\Modules\Settings\Models\Client;
use App\Modules\Settings\Resources\ClientResource;
use App\Modules\Settings\Resources\LivraisonNonFixeeResource;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClientService
{
    /**
     * 🔹 Créer un nouveau client
     */
    public function store(array $data)
    {
        try {
            $data['created_by'] = Auth::id();
            $client             = Client::create($data)->refresh();

            return response()->json([
                'status'  => 200,
                'message' => 'Client créé avec succès.',
                'data'    => new ClientResource(
                    $client->with(['createur', 'modificateur', 'initLivraisons', 'fixings'])->first()
                ),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création du client.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Mettre à jour un client
     */
    public function update(int $id, array $data)
    {
        try {
            $client            = Client::findOrFail($id);
            $data['modify_by'] = Auth::id();
            $client->update($data);

            $client = Client::with(['createur', 'modificateur', 'initLivraisons', 'fixings'])
                ->find($id);

            return response()->json([
                'status'  => 200,
                'message' => 'Client mis à jour avec succès.',
                'data'    => new ClientResource($client),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour du client.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Supprimer un client
     */
    public function delete(int $id)
    {
        try {
            Client::findOrFail($id)->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Client supprimé avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression du client.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Récupérer tous les clients
     */
    public function getAll()
    {
        try {
            $clients = Client::with(['createur', 'modificateur', 'initLivraisons', 'fixings'])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status'  => 200,
                'message' => 'Liste des clients récupérée avec succès.',
                'data'    => ClientResource::collection($clients),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des clients.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Récupérer un client spécifique
     */
    public function getOne(int $id)
    {
        try {
            $client = Client::with(['createur', 'modificateur', 'initLivraisons', 'fixings'])
                ->findOrFail($id);

            return response()->json([
                'status'  => 200,
                'message' => 'Client trouvé avec succès.',
                'data'    => new ClientResource($client),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'Client non trouvé.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Récupérer les livraisons non fixées
     */
    public function getLivraisonsNonFixees(int $clientId)
    {
        try {
            $livraisons = InitLivraison::with(['fondations'])
                ->where('id_client', $clientId)
                ->orderByDesc('id')
                ->get();

            return response()->json([
                'status'  => 200,
                'message' => 'Livraisons non fixées récupérées avec succès.',
                'data'    => LivraisonNonFixeeResource::collection($livraisons),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des livraisons non fixées.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Calcul du solde par devise (USD / GNF)
     */
    // public function calculerSoldeClient(int $id_client): array
    // {
    //     // 🔹 Fonction interne pour calculer le total par devise et par nature
    //     $getTotalParDevise = function (string $deviseSymbole, int $nature) use ($id_client) {
    //         return OperationClient::where('id_client', $id_client)
    //             ->whereHas('typeOperation', fn($q) => $q->where('nature', $nature)) // 1 = entrée, 0 = sortie
    //             ->whereHas('devise', fn($q) => $q->where('symbole', $deviseSymbole))
    //             ->sum('montant');
    //     };

    //     // 🔹 Totaux des opérations
    //     $entreesUSD = $getTotalParDevise('USD', 1);
    //     $entreesGNF = $getTotalParDevise('GNF', 1);

    //     $sortiesUSD = $getTotalParDevise('USD', 0);
    //     $sortiesGNF = $getTotalParDevise('GNF', 0);

    //     // 🔹 Factures (sorties automatiques liées aux fixings)
    //     $fixings = FixingClient::with('devise')->where('id_client', $id_client)->get();

    //     foreach ($fixings as $fixing) {
    //         $calcul  = app(FixingClientService::class)->calculerFacture($fixing->id);
    //         $montant = $calcul['total_facture'] ?? 0;

    //         if ($fixing->devise?->symbole === 'USD') {
    //             $sortiesUSD += $montant;
    //         } elseif ($fixing->devise?->symbole === 'GNF') {
    //             $sortiesGNF += $montant;
    //         }
    //     }

    //     // 🔹 Solde final
    //     return [
    //         'solde_usd' => round($entreesUSD - $sortiesUSD, 2),
    //         'solde_gnf' => round($entreesGNF - $sortiesGNF, 2),
    //     ];
    // }

    public function calculerSoldeClient(int $id_client): array
    {
        // 🔹 Fonction interne pour calculer le total par devise et par nature
        $getTotalParDevise = function (string $deviseSymbole, int $nature) use ($id_client) {
            return OperationClient::where('id_client', $id_client)
                ->whereHas('typeOperation', fn($q) => $q->where('nature', $nature)) // 1 = entrée, 0 = sortie
                ->whereHas('devise', fn($q) => $q->where('symbole', $deviseSymbole))
                ->sum('montant');
        };

        // 🔹 Totaux des opérations
        $entreesUSD = $getTotalParDevise('USD', 1);
        $entreesGNF = $getTotalParDevise('GNF', 1);

        $sortiesUSD = $getTotalParDevise('USD', 0);
        $sortiesGNF = $getTotalParDevise('GNF', 0);

        // 🔹 Factures (sorties automatiques liées aux fixings)
        $fixings = FixingClient::with('devise')->where('id_client', $id_client)->get();

        foreach ($fixings as $fixing) {
            $calcul  = app(FixingClientService::class)->calculerFacture($fixing->id);
            $montant = $calcul['total_facture'] ?? 0;

            if ($fixing->devise?->symbole === 'USD') {
                $sortiesUSD += $montant;
            } elseif ($fixing->devise?->symbole === 'GNF') {
                $sortiesGNF += $montant;
            }
        }

        // 🔹 Solde final
        return [
            'solde_usd'   => round($entreesUSD - $sortiesUSD, 2),
            'solde_gnf'   => round($entreesGNF - $sortiesGNF, 2),

            // ✅ Ajout demandé : flux des opérations
            'entrees_usd' => round($entreesUSD, 2),
            'sorties_usd' => round($sortiesUSD, 2),
            'entrees_gnf' => round($entreesGNF, 2),
            'sorties_gnf' => round($sortiesGNF, 2),
        ];
    }

    /**
     * 🔹 Relevé complet (Fixings + Opérations)
     */

    public function getReleveClientPeriode1(int $id_client, string $date_debut, string $date_fin)
{
    try {
        $client = Client::find($id_client);

        if (! $client) {
            return response()->json([
                'status'  => 404,
                'message' => 'Client introuvable.',
                'data'    => [],
            ], 404);
        }

        $releve = $this->getReleveClientParPeriode($id_client, $date_debut, $date_fin);

        return response()->json([
            'status'  => 200,
            'message' => 'Relevé du client récupéré avec succès.',
            'data'    => $releve,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 500,
            'message' => 'Erreur lors de la récupération du relevé du client.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


    public function getReleveClient(int $id_client): array
    {
        $operationsClient = OperationClient::with(['typeOperation', 'devise'])
            ->where('id_client', $id_client)
            ->get()
            ->map(function ($op) {
                $nature = $op->typeOperation?->nature; // 1 = entrée, 0 = sortie
                return [
                    'date'           => $op->created_at?->format('Y-m-d H:i:s'),
                    'date_operation' => $op->date_operation,
                    'reference'      => $op->reference,
                    'type'           => 'operation_client',
                    'libelle'        => $op->typeOperation?->libelle ?? 'Opération client',
                    'devise'         => $op->devise?->symbole ?? '',
                    'debit'          => $nature == 0 ? (float) $op->montant : 0,
                    'credit'         => $nature == 1 ? (float) $op->montant : 0,
                ];
            });

        $fixings = FixingClient::with(['devise'])
            ->where('id_client', $id_client)
            ->get()
            ->map(function ($fix) {
                $calcul    = app(FixingClientService::class)->calculerFacture($fix->id);
                $purete    = $calcul['purete_totale'] ?? 0;
                $bourse    = $calcul['bourse'] ?? 0;
                $discompte = $calcul['discompte'] ?? 0;

                return [
                    'date'           => $fix->created_at?->format('Y-m-d H:i:s'),
                    'date_operation' => null,
                    'reference'      => $fix->reference ?? null,
                    'type'           => 'fixing',
                    'libelle'        => "Facturation du {$purete} g | Bourse : {$bourse} | Discompte : {$discompte}",
                    'devise' => $fix->devise?->symbole ?? '',
                    'debit'  => (float) ($calcul['total_facture'] ?? 0),
                    'credit' => 0,
                ];
            });

        // ✅ Fusion et tri du plus ancien au plus récent pour calcul des soldes
        $data = $operationsClient->concat($fixings)
            ->sortBy('date')
            ->values()
            ->toArray();

        $soldeUSD = 0;
        $soldeGNF = 0;
        $usdList  = [];
        $gnfList  = [];

        // ✅ Calcul des soldes progressifs
        foreach ($data as $ligne) {
            if ($ligne['devise'] === 'USD') {
                $soldeUSD += $ligne['credit'] - $ligne['debit'];
                $ligne['solde_apres'] = round($soldeUSD, 2);
                $usdList[]            = $ligne;
            } elseif ($ligne['devise'] === 'GNF') {
                $soldeGNF += $ligne['credit'] - $ligne['debit'];
                $ligne['solde_apres'] = round($soldeGNF, 2);
                $gnfList[]            = $ligne;
            }
        }

        // ✅ Inversion pour afficher du plus récent au plus ancien
        $usdList = array_reverse($usdList);
        $gnfList = array_reverse($gnfList);

        return [
            'usd' => $usdList,
            'gnf' => $gnfList,
        ];
    }

    public function calculerStockClient(int $id_client): array
    {
        // 🔹 Récupération de toutes les livraisons du client
        $livraisonIds = InitLivraison::where('id_client', $id_client)->pluck('id');

        if ($livraisonIds->isEmpty()) {
            return [
                'id_client'    => $id_client,
                'total_livre'  => 0.0,
                'total_fixing' => 0.0,
                'reste_stock'  => 0.0,
            ];
        }

        // 🔹 Total livré : toutes les fondations issues des expéditions de ces livraisons
        $totalLivre = Fondation::whereHas('expedition', function ($q) use ($livraisonIds) {
            $q->whereIn('id_init_livraison', $livraisonIds);
        })
            ->sum('poids_fondu');

        // 🔹 Total fixé : uniquement les fondations dont id_fixing n'est pas null
        $totalFixing = Fondation::whereHas('expedition', function ($q) use ($livraisonIds) {
            $q->whereIn('id_init_livraison', $livraisonIds);
        })
            ->whereNotNull('id_fixing')
            ->sum('poids_fondu');

        // 🔹 Calcul du reste
        $resteStock = max($totalLivre - $totalFixing, 0);

        return [
            'id_client'    => $id_client,
            'total_livre'  => round((float) $totalLivre, 2),
            'total_fixing' => round((float) $totalFixing, 2),
            'reste_stock'  => round((float) $resteStock, 2),
        ];
    }

    public function truncateDatabaseExcept(array $except = [])
    {
        // ✅ Tables Laravel par défaut qu’on ne vide pas
        $defaultExcept = [
            'migrations',
            'users',
            'password_resets',
            'failed_jobs',
            'personal_access_tokens',

        ];

        $except = array_merge($defaultExcept, $except);

        // Désactiver les contraintes de clés étrangères
        Schema::disableForeignKeyConstraints();

        // Récupérer toutes les tables via information_schema
        $tables = DB::select('SHOW TABLES');
        $tables = array_map('current', $tables); // transformer l’objet en simple tableau

        foreach ($tables as $table) {
            if (! in_array($table, $except)) {
                DB::table($table)->truncate();
            }
        }

        // Réactiver les contraintes
        Schema::enableForeignKeyConstraints();

        return response()->json([
            'status'  => 200,
            'message' => 'Base de données vidée avec succès (sauf tables exclues).',
        ]);
    }

    //cette fonction permet de rechercher la situaton du client entre deux dates

    public function getReleveClientParPeriode(int $id_client, string $date_debut, string $date_fin): array
    {
        // ✅ Récupération des opérations du client entre deux dates
        $operationsClient = OperationClient::with(['typeOperation', 'devise'])
            ->where('id_client', $id_client)
            ->whereBetween('created_at', [$date_debut, $date_fin])
            ->get()
            ->map(function ($op) {
                $nature = $op->typeOperation?->nature; // 1 = entrée, 0 = sortie

                return [
                    'date'           => $op->created_at?->format('Y-m-d H:i:s'),
                    'date_operation' => $op->date_operation,
                    'reference'      => $op->reference,
                    'type'           => 'operation_client',
                    'libelle'        => $op->typeOperation?->libelle ?? 'Opération client',
                    'devise'         => $op->devise?->symbole ?? '',
                    'debit'          => $nature == 0 ? (float) $op->montant : 0,
                    'credit'         => $nature == 1 ? (float) $op->montant : 0,
                ];
            });

        // ✅ Récupération des fixings du client entre deux dates
        $fixings = FixingClient::with(['devise'])
            ->where('id_client', $id_client)
            ->whereBetween('created_at', [$date_debut, $date_fin])
            ->get()
            ->map(function ($fix) {
                $calcul = app(FixingClientService::class)->calculerFacture($fix->id);

                return [
                    'date'           => $fix->created_at?->format('Y-m-d H:i:s'),
                    'date_operation' => null,
                    'reference'      => $fix->reference ?? null,
                    'type'           => 'fixing',
                    'libelle'        => "Facturation du {$calcul['purete_totale']} g | Bourse: {$calcul['bourse']} | Discompte: {$calcul['discompte']}",
                    'devise' => $fix->devise?->symbole ?? '',
                    'debit'  => (float) ($calcul['total_facture'] ?? 0),
                    'credit' => 0,
                ];
            });

        // ✅ Fusion, tri du plus ancien au plus récent
        $data = $operationsClient
            ->concat($fixings)
            ->sortBy('date')
            ->values()
            ->toArray();

        // ✅ Calcul des soldes progressifs
        $soldeUSD = 0;
        $soldeGNF = 0;
        $usdList  = [];
        $gnfList  = [];

        foreach ($data as $ligne) {
            if ($ligne['devise'] === 'USD') {
                $soldeUSD += $ligne['credit'] - $ligne['debit'];
                $ligne['solde_apres'] = round($soldeUSD, 2);
                $usdList[]            = $ligne;
            } elseif ($ligne['devise'] === 'GNF') {
                $soldeGNF += $ligne['credit'] - $ligne['debit'];
                $ligne['solde_apres'] = round($soldeGNF, 2);
                $gnfList[]            = $ligne;
            }
        }

        // ✅ Inversion pour afficher du plus récent au plus ancien
        $usdList = array_reverse($usdList);
        $gnfList = array_reverse($gnfList);

        return [
            'usd' => $usdList,
            'gnf' => $gnfList,
        ];
    }

}

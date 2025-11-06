<?php
namespace App\Modules\Settings\Services;

use App\Modules\Comptabilite\Models\OperationClient;
use App\Modules\Fixing\Models\FixingClient;
use App\Modules\Fixing\Models\InitLivraison;
use App\Modules\Fixing\Services\FixingClientService;
use App\Modules\Fondation\Models\Fondation;
use App\Modules\Settings\Models\Client;
use App\Modules\Settings\Models\Devise;
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

    

    
    // public function calculerSoldeClient(int $id_client): array
    // {
    //     // 🔹 Récupérer toutes les devises actives
    //     $devises = Devise::select('id', 'symbole')->get();

    //     $soldes = [];

    //     foreach ($devises as $devise) {
    //         // 🔸 Convertir le symbole en minuscule
    //         $symbole = strtolower($devise->symbole);

    //         // 🔸 Calcul du total par nature (1 = entrée, 0 = sortie)
    //         $getTotalParNature = function (int $nature) use ($id_client, $symbole) {
    //             return OperationClient::where('id_client', $id_client)
    //                 ->whereHas('typeOperation', fn($q) => $q->where('nature', $nature))
    //                 ->whereHas('devise', fn($q) => $q->whereRaw('LOWER(symbole) = ?', [$symbole]))
    //                 ->sum('montant');
    //         };

    //         $entrees = $getTotalParNature(1);
    //         $sorties = $getTotalParNature(0);

    //         // 🔹 Ajouter les factures (fixings)
    //         $fixings = FixingClient::with('devise')
    //             ->where('id_client', $id_client)
    //             ->whereHas('devise', fn($q) => $q->whereRaw('LOWER(symbole) = ?', [$symbole]))
    //             ->get();

    //         foreach ($fixings as $fixing) {
    //             $calcul  = app(FixingClientService::class)->calculerFacture($fixing->id);
    //             $montant = $calcul['total_facture'] ?? 0;
    //             $sorties += $montant;
    //         }

    //         // 🔹 Stocker le solde par devise (clé en minuscule)
    //         $soldes[$symbole] = round($entrees - $sorties, 2);
    //     }

    //     return $soldes;
    // }
    public function calculerSoldeClient(int $id_client): array
{
    // 🔹 Récupérer toutes les devises actives
    $devises = Devise::select('id', 'symbole')->get();

    $soldes = [];
    $flux   = [];

    foreach ($devises as $devise) {
        $symbole = strtolower($devise->symbole);

        // 🔸 Fonction pour totaliser par nature (1 = entrée, 0 = sortie)
        $getTotalParNature = function (int $nature) use ($id_client, $symbole) {
            return OperationClient::where('id_client', $id_client)
                ->whereHas('typeOperation', fn($q) => $q->where('nature', $nature))
                ->whereHas('devise', fn($q) => $q->whereRaw('LOWER(symbole) = ?', [$symbole]))
                ->sum('montant');
        };

        // 🔹 Totaux d’opérations
        $entrees = $getTotalParNature(1);
        $sorties = $getTotalParNature(0);

        // 🔹 Ajouter les factures (fixings)
        $fixings = FixingClient::with('devise')
            ->where('id_client', $id_client)
            ->whereHas('devise', fn($q) => $q->whereRaw('LOWER(symbole) = ?', [$symbole]))
            ->get();

        foreach ($fixings as $fixing) {
            $calcul  = app(FixingClientService::class)->calculerFacture($fixing->id);
            $montant = $calcul['total_facture'] ?? 0;
            $sorties += $montant;
        }

        // 🔹 Calcul du solde final pour la devise
        $solde = $entrees - $sorties;

        // 🔹 Enregistrement
        $flux[$symbole] = [
            'entrees' => round($entrees, 2),
            'sorties' => round($sorties, 2),
        ];

        $soldes[$symbole] = round($solde, 2);
    }

    // 🔹 Structure finale uniforme avec calculerSoldeDivers
    return [
        'soldes' => $soldes,
        'flux'   => $flux,
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

    // public function getReleveClient(int $id_client): array
    // {
    //     $operationsClient = OperationClient::with(['typeOperation', 'devise'])
    //         ->where('id_client', $id_client)
    //         ->get()
    //         ->map(function ($op) {
    //             $nature = $op->typeOperation?->nature; // 1 = entrée, 0 = sortie
    //             return [
    //                 'date'           => $op->created_at?->format('Y-m-d H:i:s'),
    //                 'date_operation' => $op->date_operation,
    //                 'reference'      => $op->reference,
    //                 'type'           => 'operation_client',
    //                 'libelle'        => $op->typeOperation?->libelle ?? 'Opération client',
    //                 'devise'         => $op->devise?->symbole ?? '',
    //                 'debit'          => $nature == 0 ? (float) $op->montant : 0,
    //                 'credit'         => $nature == 1 ? (float) $op->montant : 0,
    //             ];
    //         });

    //     $fixings = FixingClient::with(['devise'])
    //         ->where('id_client', $id_client)
    //         ->get()
    //         ->map(function ($fix) {
    //             $calcul    = app(FixingClientService::class)->calculerFacture($fix->id);
    //             $purete    = $calcul['purete_totale'] ?? 0;
    //             $bourse    = $calcul['bourse'] ?? 0;
    //             $discompte = $calcul['discompte'] ?? 0;

    //             return [
    //                 'date'           => $fix->created_at?->format('Y-m-d H:i:s'),
    //                 'date_operation' => null,
    //                 'reference'      => $fix->reference ?? null,
    //                 'type'           => 'fixing',
    //                 'libelle'        => "Facturation du {$purete} g | Bourse : {$bourse} | Discompte : {$discompte}",
    //                 'devise' => $fix->devise?->symbole ?? '',
    //                 'debit'  => (float) ($calcul['total_facture'] ?? 0),
    //                 'credit' => 0,
    //             ];
    //         });

    //     // ✅ Fusion et tri du plus ancien au plus récent pour calcul des soldes
    //     $data = $operationsClient->concat($fixings)
    //         ->sortBy('date')
    //         ->values()
    //         ->toArray();

    //     $soldeUSD = 0;
    //     $soldeGNF = 0;
    //     $usdList  = [];
    //     $gnfList  = [];

    //     // ✅ Calcul des soldes progressifs
    //     foreach ($data as $ligne) {
    //         if ($ligne['devise'] === 'USD') {
    //             $soldeUSD += $ligne['credit'] - $ligne['debit'];
    //             $ligne['solde_apres'] = round($soldeUSD, 2);
    //             $usdList[]            = $ligne;
    //         } elseif ($ligne['devise'] === 'GNF') {
    //             $soldeGNF += $ligne['credit'] - $ligne['debit'];
    //             $ligne['solde_apres'] = round($soldeGNF, 2);
    //             $gnfList[]            = $ligne;
    //         }
    //     }

    //     // ✅ Inversion pour afficher du plus récent au plus ancien
    //     $usdList = array_reverse($usdList);
    //     $gnfList = array_reverse($gnfList);

    //     return [
    //         'usd' => $usdList,
    //         'gnf' => $gnfList,
    //     ];
    // }

    // public function getReleveClient(int $id_client): array
    // {
    //     // 🔹 Récupérer toutes les devises actives
    //     $devises = Devise::pluck('symbole')->map(fn($s) => strtolower($s));

    //     // 🔹 1. Récupérer les opérations du client
    //     $operationsClient = OperationClient::with(['typeOperation', 'devise'])
    //         ->where('id_client', $id_client)
    //         ->get()
    //         ->map(function ($op) {
    //             $nature = $op->typeOperation?->nature; // 1 = entrée, 0 = sortie
    //             return [
    //                 'date'           => $op->created_at?->format('Y-m-d H:i:s'),
    //                 'date_operation' => $op->date_operation,
    //                 'reference'      => $op->reference,
    //                 'type'           => 'operation_client',
    //                 'libelle'        => $op->typeOperation?->libelle ?? 'Opération client',
    //                 'devise'         => strtolower($op->devise?->symbole ?? ''),
    //                 'debit'          => $nature == 0 ? (float) $op->montant : 0,
    //                 'credit'         => $nature == 1 ? (float) $op->montant : 0,
    //             ];
    //         });

    //     // 🔹 2. Récupérer les fixings du client
    //     $fixings = FixingClient::with(['devise'])
    //         ->where('id_client', $id_client)
    //         ->get()
    //         ->map(function ($fix) {
    //             $calcul    = app(FixingClientService::class)->calculerFacture($fix->id);
    //             $purete    = $calcul['purete_totale'] ?? 0;
    //             $bourse    = $calcul['bourse'] ?? 0;
    //             $discompte = $calcul['discompte'] ?? 0;

    //             return [
    //                 'date'           => $fix->created_at?->format('Y-m-d H:i:s'),
    //                 'date_operation' => null,
    //                 'reference'      => $fix->reference ?? null,
    //                 'type'           => 'fixing',
    //                 'libelle'        => "Facturation du {$purete} g | Bourse : {$bourse} | Discompte : {$discompte}",
    //                 'devise' => strtolower($fix->devise?->symbole ?? ''),
    //                 'debit'  => (float) ($calcul['total_facture'] ?? 0),
    //                 'credit' => 0,
    //             ];
    //         });

    //     // 🔹 3. Fusionner toutes les opérations
    //     $data = $operationsClient->concat($fixings)
    //         ->sortBy('date')
    //         ->values()
    //         ->toArray();

    //     // 🔹 4. Initialiser les soldes par devise
    //     $soldes    = [];
    //     $resultats = [];

    //     foreach ($devises as $symbole) {
    //         $soldes[$symbole]    = 0;
    //         $resultats[$symbole] = [];
    //     }

    //     // 🔹 5. Calcul des soldes progressifs dynamiques
    //     foreach ($data as $ligne) {
    //         $symbole = $ligne['devise'];

    //         if (! isset($soldes[$symbole])) {
    //             $soldes[$symbole]    = 0;
    //             $resultats[$symbole] = [];
    //         }

    //         $soldes[$symbole] += $ligne['credit'] - $ligne['debit'];
    //         $ligne['solde_apres']  = round($soldes[$symbole], 2);
    //         $resultats[$symbole][] = $ligne;
    //     }

    //     // 🔹 6. Inverser les listes (du plus récent au plus ancien)
    //     foreach ($resultats as $symbole => &$list) {
    //         $list = array_reverse($list);
    //     }

    //     return $resultats;
    // }
    public function getReleveClient(int $id_client): array
    {
        // 🔹 Récupérer toutes les devises actives
        $devises = Devise::pluck('symbole')->map(fn($s) => strtolower($s));

        // 🔹 1. Récupérer les opérations du client avec banque
        $operationsClient = OperationClient::with(['typeOperation', 'devise', 'compte.banque'])
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
                    'banque'         => $op->compte?->banque?->libelle ?? null, // ✅ libellé banque
                    'numero_compte'  => $op->compte?->numero_compte ?? null,    // ✅ numéro du compte (si existe)
                    'devise'         => strtolower($op->devise?->symbole ?? ''),
                    'debit'          => $nature == 0 ? (float) $op->montant : 0,
                    'credit'         => $nature == 1 ? (float) $op->montant : 0,
                ];
            });

        // 🔹 2. Récupérer les fixings du client
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
                    'banque'        => null, // aucun lien bancaire pour un fixing
                    'numero_compte' => null,
                    'devise'        => strtolower($fix->devise?->symbole ?? ''),
                    'debit'         => (float) ($calcul['total_facture'] ?? 0),
                    'credit'        => 0,
                ];
            });

        // 🔹 3. Fusionner toutes les opérations
        $data = $operationsClient->concat($fixings)
            ->sortBy('date')
            ->values()
            ->toArray();

        // 🔹 4. Initialiser les soldes par devise
        $soldes    = [];
        $resultats = [];

        foreach ($devises as $symbole) {
            $soldes[$symbole]    = 0;
            $resultats[$symbole] = [];
        }

        // 🔹 5. Calcul des soldes progressifs dynamiques
        foreach ($data as $ligne) {
            $symbole = $ligne['devise'];

            if (! isset($soldes[$symbole])) {
                $soldes[$symbole]    = 0;
                $resultats[$symbole] = [];
            }

            $soldes[$symbole] += $ligne['credit'] - $ligne['debit'];
            $ligne['solde_apres']  = round($soldes[$symbole], 2);
            $resultats[$symbole][] = $ligne;
        }

        // 🔹 6. Inverser les listes (du plus récent au plus ancien)
        foreach ($resultats as $symbole => &$list) {
            $list = array_reverse($list);
        }

        return $resultats;
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

    // public function getReleveClientParPeriode(int $id_client, string $date_debut, string $date_fin): array
    // {
    //     // ✅ Récupération des opérations du client entre deux dates
    //     $operationsClient = OperationClient::with(['typeOperation', 'devise'])
    //         ->where('id_client', $id_client)
    //         ->whereBetween('created_at', [$date_debut, $date_fin])
    //         ->get()
    //         ->map(function ($op) {
    //             $nature = $op->typeOperation?->nature; // 1 = entrée, 0 = sortie

    //             return [
    //                 'date'           => $op->created_at?->format('Y-m-d H:i:s'),
    //                 'date_operation' => $op->date_operation,
    //                 'reference'      => $op->reference,
    //                 'type'           => 'operation_client',
    //                 'libelle'        => $op->typeOperation?->libelle ?? 'Opération client',
    //                 'devise'         => $op->devise?->symbole ?? '',
    //                 'debit'          => $nature == 0 ? (float) $op->montant : 0,
    //                 'credit'         => $nature == 1 ? (float) $op->montant : 0,
    //             ];
    //         });

    //     // ✅ Récupération des fixings du client entre deux dates
    //     $fixings = FixingClient::with(['devise'])
    //         ->where('id_client', $id_client)
    //         ->whereBetween('created_at', [$date_debut, $date_fin])
    //         ->get()
    //         ->map(function ($fix) {
    //             $calcul = app(FixingClientService::class)->calculerFacture($fix->id);

    //             return [
    //                 'date'           => $fix->created_at?->format('Y-m-d H:i:s'),
    //                 'date_operation' => null,
    //                 'reference'      => $fix->reference ?? null,
    //                 'type'           => 'fixing',
    //                 'libelle'        => "Facturation du {$calcul['purete_totale']} g | Bourse: {$calcul['bourse']} | Discompte: {$calcul['discompte']}",
    //                 'devise' => $fix->devise?->symbole ?? '',
    //                 'debit'  => (float) ($calcul['total_facture'] ?? 0),
    //                 'credit' => 0,
    //             ];
    //         });

    //     // ✅ Fusion, tri du plus ancien au plus récent
    //     $data = $operationsClient
    //         ->concat($fixings)
    //         ->sortBy('date')
    //         ->values()
    //         ->toArray();

    //     // ✅ Calcul des soldes progressifs
    //     $soldeUSD = 0;
    //     $soldeGNF = 0;
    //     $usdList  = [];
    //     $gnfList  = [];

    //     foreach ($data as $ligne) {
    //         if ($ligne['devise'] === 'USD') {
    //             $soldeUSD += $ligne['credit'] - $ligne['debit'];
    //             $ligne['solde_apres'] = round($soldeUSD, 2);
    //             $usdList[]            = $ligne;
    //         } elseif ($ligne['devise'] === 'GNF') {
    //             $soldeGNF += $ligne['credit'] - $ligne['debit'];
    //             $ligne['solde_apres'] = round($soldeGNF, 2);
    //             $gnfList[]            = $ligne;
    //         }
    //     }

    //     // ✅ Inversion pour afficher du plus récent au plus ancien
    //     $usdList = array_reverse($usdList);
    //     $gnfList = array_reverse($gnfList);

    //     return [
    //         'usd' => $usdList,
    //         'gnf' => $gnfList,
    //     ];
    // }
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

        // ✅ Fusion des opérations et fixings
        $data = $operationsClient
            ->concat($fixings)
            ->sortBy('date') // plus ancien → plus récent
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

        // ✅ Retour structuré
        return [
            'usd' => $usdList,
            'gnf' => $gnfList,
        ];
    }

}

<?php
namespace App\Modules\Settings\Services;

use App\Modules\Comptabilite\Models\OperationClient;
use App\Modules\Fixing\Models\FixingClient;
use App\Modules\Fixing\Models\InitLivraison;
use App\Modules\Fixing\Resources\FixingProvisoireResource;
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

            $client = Client::with(['createur', 'modificateur', 'fixings'])
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
            $clients = Client::with(['createur', 'modificateur', 'fixings'])
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
            $client = Client::with(['createur', 'modificateur', 'fixings'])
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
    public function getFixingsProvisoiresByClient(int $id_client)
    {
        try {
            $fixings = FixingClient::provisoires() // utilise ton scopeProvisoires()
                ->where('id_client', $id_client)
                ->with(['client', 'devise', 'createur', 'modificateur'])
                ->orderByDesc('created_at')
                ->get();

            if ($fixings->isEmpty()) {
                return response()->json([
                    'status'  => 404,
                    'message' => "Aucun fixing provisoire trouvé pour ce client.",
                ], 404);
            }

            return response()->json([
                'status'  => 200,
                'message' => "Fixings provisoires du client récupérés avec succès.",
                'data'    => FixingProvisoireResource::collection($fixings),
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => "Erreur lors de la récupération des fixings provisoires.",
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

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

            // 🔹 Calcul du solde final
            $solde = $entrees - $sorties;

            // 🔹 Enregistrement du flux
            $flux[] = [
                'devise'  => $symbole,
                'entrees' => round($entrees, 2),
                'sorties' => round($sorties, 2),
            ];

            // 🔹 Enregistrement du solde (format attendu par le front)
            $soldes[] = [
                'devise'  => $symbole,
                'montant' => round($solde, 2),
            ];
        }

        // 🔹 Structure finale uniforme
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

        // 🔹 Récupérer toutes les devises actives
        $devises = Devise::pluck('symbole')
            ->map(fn($s) => strtolower($s))
            ->unique()
            ->values()
            ->all();

        // ==========================================================
        // 🔹 1. OPERATIONS (filtrées entre deux dates)
        // ==========================================================
        $operations = OperationClient::with(['typeOperation', 'devise', 'compte.banque'])
            ->where('id_client', $id_client)
            ->whereBetween('created_at', [$date_debut, $date_fin])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($op) {

                $nature = (int) ($op->typeOperation?->nature ?? 0);
                $devise = strtolower($op->devise?->symbole ?? 'gnf');

                return [
                    'type'                => 'operation',
                    'date'                => $op->created_at?->format('Y-m-d H:i:s'),
                    'reference_operation' => $op->reference,
                    'libelle_operation'   => $op->typeOperation?->libelle ?? 'Opération client',
                    'banque'              => $op->compte?->banque?->libelle ?? null,
                    'numero_compte'       => $op->compte?->numero_compte ?? null,
                    'devise'              => $devise,
                    'debit'               => $nature === 0 ? (float) $op->montant : 0.0,
                    'credit'              => $nature === 1 ? (float) $op->montant : 0.0,
                    'solde_avant'         => 0.0,
                    'solde_apres'         => 0.0,
                    'solde_apres_fixing'  => 0.0,
                    'reference_fixing'    => null,
                    'libelle_fixing'      => null,
                    'poids_entree'        => 0.0,
                    'poids_sortie'        => 0.0,
                    'stock_avant'         => 0.0,
                    'stock_apres'         => 0.0,
                    'total_facture'       => 0.0,
                ];
            });

        // ==========================================================
        // 🔹 2. FIXINGS (filtrés entre deux dates)
        // ==========================================================
        $fixings = FixingClient::with(['devise'])
            ->where('id_client', $id_client)
            ->whereIn('status', ['vendu', 'provisoire'])
            ->whereBetween('created_at', [$date_debut, $date_fin])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($fix) {

                $calc   = app(FixingClientService::class)->calculerFacture($fix->id);
                $devise = strtolower($fix->devise?->symbole ?? 'gnf');

                $total     = (float) ($calc['total_facture'] ?? 0.0);
                $poids     = (float) ($calc['poids_total'] ?? 0.0);
                $prixU     = (float) ($calc['prix_unitaire'] ?? 0.0);
                $discompte = (float) ($fix->discompte ?? 0.0);
                $bourse    = (float) ($fix->bourse ?? 0.0);

                return [
                    'type'                => 'fixing',
                    'date'                => $fix->created_at?->format('Y-m-d H:i:s'),
                    'reference_operation' => null,
                    'libelle_operation'   => null,
                    'banque'              => null,
                    'numero_compte'       => null,
                    'devise'              => $devise,
                    'debit'               => $total,
                    'credit'              => 0.0,
                    'solde_avant'         => 0.0,
                    'solde_apres'         => 0.0,
                    'solde_apres_fixing'  => 0.0,
                    'reference_fixing'    => 'FIX-' . str_pad($fix->id, 5, '0', STR_PAD_LEFT),
                    'libelle_fixing'      => "Fixing  or : {$poids} g à {$prixU} /g , Bourse: {$bourse}",
                    'poids_entree'  => 0.0,
                    'poids_sortie'  => $poids,
                    'stock_avant'   => 0.0,
                    'stock_apres'   => 0.0,
                    'total_facture' => $total,
                ];
            });

        // ==========================================================
        // 🔹 3. LIVRAISONS (filtrées entre deux dates)
        // ==========================================================
        $livraisons = InitLivraison::where('id_client', $id_client)
            ->whereBetween('created_at', [$date_debut, $date_fin])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($livraison) {

                $purete_totale = Fondation::whereHas('expedition', function ($q) use ($livraison) {
                    $q->where('id_init_livraison', $livraison->id);
                })
                    ->get()
                    ->sum(function ($fondation) {

                        // Choix du poids + carat (Dubai si existant)
                        $poids = ($fondation->poids_dubai > 0)
                            ? (float) $fondation->poids_dubai
                            : (float) $fondation->poids_fondu;

                        $carrat = ($fondation->carrat_dubai > 0)
                            ? (float) $fondation->carrat_dubai
                            : (float) $fondation->carrat_fondu;

                        // 👉 ROUND SUR CHAQUE PRODUIT
                        return round(($poids * $carrat) / 24, 2);
                    });

                $purete_locale_totale = Fondation::whereHas('expedition', function ($q) use ($livraison) {
                    $q->where('id_init_livraison', $livraison->id);
                })
                    ->get()
                    ->sum(function ($fondation) {

                        // 🔥 Round à chaque produit
                        return round(
                            ($fondation->poids_fondu * $fondation->carrat_fondu) / 24,
                            2
                        );
                    });
                $purete_locale_totale = round($purete_locale_totale, 2);
                $poids_entree         = round($purete_totale, 2);

                return [
                    'type'                => 'livraison',
                    'date'                => $livraison->created_at?->format('Y-m-d H:i:s'),
                    'reference_operation' => $livraison->reference ?? null,
                    'libelle_operation'   => "Livraison d’or de poids : {$purete_locale_totale}",

                    'banque'             => null,
                    'numero_compte'      => null,
                    'devise'             => 'usd',
                    'debit'              => 0.0,
                    'credit'             => 0.0,
                    'solde_avant'        => 0.0,
                    'solde_apres'        => 0.0,
                    'solde_apres_fixing' => 0.0,
                    'reference_fixing'   => null,
                    'libelle_fixing'     => null,
                    'poids_entree'       => $poids_entree,
                    'poids_sortie'       => 0.0,
                    'stock_avant'        => 0.0,
                    'stock_apres'        => 0.0,
                    'total_facture'      => 0.0,
                ];
            });

        // ==========================================================
        // 🔹 4. Fusion chronologique (ASC)
        // ==========================================================
        $rows = $operations->concat($fixings)->concat($livraisons)
            ->sortBy('date')
            ->values()
            ->all();

        // ==========================================================
        // 🔹 5. Initialisation
        // ==========================================================
        $soldes      = [];
        $grouped     = [];
        $stockGlobal = 0.0;

        foreach ($devises as $sym) {
            $soldes[$sym]  = 0.0;
            $grouped[$sym] = [];
        }

        // ==========================================================
        // 🔹 6. Calcul chronologique
        // ==========================================================
        foreach ($rows as $ligne) {

            $sym = $ligne['devise'] ?: 'gnf';

            if (! isset($grouped[$sym])) {
                $grouped[$sym] = [];
                $soldes[$sym]  = 0.0;
            }

            $solde_avant = $soldes[$sym];
            $stock_avant = $stockGlobal;

            // 💰 Calcul du solde
            $soldes[$sym] += ((float) $ligne['credit'] - (float) $ligne['debit']);

            // ⚖️ Gestion du stock d'or
            if ($ligne['type'] === 'fixing') {
                $stockGlobal -= (float) $ligne['poids_sortie'];
            } elseif ($ligne['type'] === 'livraison') {
                $stockGlobal += (float) $ligne['poids_entree'];
            }

            $ligne['solde_avant']        = round($solde_avant, 2);
            $ligne['solde_apres']        = round($soldes[$sym], 2);
            $ligne['solde_apres_fixing'] = round($soldes[$sym], 2);
            $ligne['stock_avant']        = round($stock_avant, 3);
            $ligne['stock_apres']        = round($stockGlobal, 3);

            $grouped[$sym][] = $ligne;
        }

        // ==========================================================
        // 🔹 7. Réponse JSON formatée
        // ==========================================================
        return response()->json([
            'status'  => 200,
            'message' => 'Relevé combiné généré avec succès.',
            'data'    => [
                'operations_financieres' => (object) $grouped,
                'stock_final'            => round($stockGlobal, 3),
            ],
        ]);

    }

    public function getReleveClient(int $id_client): array
    {
        // 🔹 1. Récupérer toutes les devises actives
        $devises = Devise::pluck('symbole')
            ->map(fn($s) => strtolower($s))
            ->unique()
            ->values()
            ->all();

        // 🔹 2. Récupérer les opérations financières
        $operations = OperationClient::with(['typeOperation', 'devise', 'compte.banque'])
            ->where('id_client', $id_client)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($op) {
                $nature = (int) ($op->typeOperation?->nature ?? 0);
                $devise = strtolower($op->devise?->symbole ?? 'gnf');

                return [
                    'type'                => 'operation',
                    'date'                => $op->created_at?->format('Y-m-d H:i:s'),
                    'reference_operation' => $op->reference,
                    'libelle_operation'   => $op->typeOperation?->libelle ?? 'Opération client',
                    'banque'              => $op->compte?->banque?->libelle ?? null,
                    'numero_compte'       => $op->compte?->numero_compte ?? null,
                    'devise'              => $devise,
                    'debit'               => $nature === 0 ? (float) $op->montant : 0.0,
                    'credit'              => $nature === 1 ? (float) $op->montant : 0.0,
                    'solde_avant'         => 0.0,
                    'solde_apres'         => 0.0,
                    'solde_apres_fixing'  => 0.0,
                    'reference_fixing'    => null,
                    'libelle_fixing'      => null,
                    'poids_entree'        => 0.0,
                    'poids_sortie'        => 0.0,
                    'stock_avant'         => 0.0,
                    'stock_apres'         => 0.0,
                    'total_facture'       => 0.0,
                ];
            });

        // 🔹 3. Récupérer les fixings
        $fixings = FixingClient::with(['devise'])
            ->where('id_client', $id_client)
            ->whereIn('status', ['vendu', 'provisoire'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($fix) {
                $calc      = app(FixingClientService::class)->calculerFacture($fix->id);
                $devise    = strtolower($fix->devise?->symbole ?? 'gnf');
                $total     = (float) ($calc['total_facture'] ?? 0.0);
                $poids     = (float) ($calc['poids_total'] ?? 0.0);
                $prixU     = (float) ($calc['prix_unitaire'] ?? 0.0);
                $discompte = (float) ($fix->discompte ?? 0.0);
                $bourse    = (float) ($fix->bourse ?? 0.0);

                return [
                    'type'                => 'fixing',
                    'date'                => $fix->created_at?->format('Y-m-d H:i:s'),
                    'reference_operation' => null,
                    'libelle_operation'   => null,
                    'banque'              => null,
                    'numero_compte'       => null,
                    'devise'              => $devise,
                    'debit'               => $total,
                    'credit'              => 0.0,
                    'solde_avant'         => 0.0,
                    'solde_apres'         => 0.0,
                    'solde_apres_fixing'  => 0.0,
                    'reference_fixing'    => 'FIX-' . str_pad($fix->id, 5, '0', STR_PAD_LEFT),
                    'libelle_fixing'      => "Fixing : {$poids} g à {$prixU} /g  Bourse: {$bourse}",
                    'poids_entree'  => 0.0,
                    'poids_sortie'  => $poids,
                    'stock_avant'   => 0.0,
                    'stock_apres'   => 0.0,
                    'total_facture' => $total,
                ];
            });
        $livraisons = InitLivraison::where('id_client', $id_client)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($livraison) {

                // 🔥 Calcul pureté totale (Dubai si disponible)
                $purete_totale = Fondation::whereHas('expedition', function ($q) use ($livraison) {
                    $q->where('id_init_livraison', $livraison->id);
                })
                    ->get()
                    ->sum(function ($fondation) {

                        // Choix du poids + carat (Dubai si existant)
                        $poids = ($fondation->poids_dubai > 0)
                            ? (float) $fondation->poids_dubai
                            : (float) $fondation->poids_fondu;

                        $carrat = ($fondation->carrat_dubai > 0)
                            ? (float) $fondation->carrat_dubai
                            : (float) $fondation->carrat_fondu;

                        // 👉 ROUND SUR CHAQUE PRODUIT
                        return round(($poids * $carrat) / 24, 2);
                    });
                $purete_locale_totale = Fondation::whereHas('expedition', function ($q) use ($livraison) {
                    $q->where('id_init_livraison', $livraison->id);
                })
                    ->get()
                    ->sum(function ($fondation) {

                        // 🔥 Round à chaque produit
                        return round(
                            ($fondation->poids_fondu * $fondation->carrat_fondu) / 24,
                            2
                        );
                    });

                //'libelle_operation'   => "Livraison d’or de poids : {$purete_locale_totale}",
                $purete_locale_totale = round($purete_locale_totale, 2);
                $poids_entree         = round($purete_totale, 2);

                return [
                    'type'                => 'livraison',
                    'date'                => $livraison->created_at?->format('Y-m-d H:i:s'),
                    'reference_operation' => $livraison->reference ?? null,
                    'libelle_operation'   => "Livraison d’or de poids : {$purete_locale_totale}",
                    'banque'             => null,
                    'numero_compte'      => null,
                    'devise'             => 'usd',
                    'debit'              => 0.0,
                    'credit'             => 0.0,
                    'solde_avant'        => 0.0,
                    'solde_apres'        => 0.0,
                    'solde_apres_fixing' => 0.0,
                    'reference_fixing'   => null,
                    'libelle_fixing'     => null,
                    'poids_entree'       => $poids_entree,
                    'poids_sortie'       => 0.0,
                    'stock_avant'        => 0.0,
                    'stock_apres'        => 0.0,
                    'total_facture'      => 0.0,
                ];
            });

        // 🔹 5. Fusion complète dans l’ordre chronologique croissant (ASC)
        $rows = $operations->concat($fixings)->concat($livraisons)->sortBy('date')->values()->all();

        // 🔹 6. Initialisation
        $soldes      = [];
        $grouped     = [];
        $stockGlobal = 0.0; // Stock global unique pour toutes les opérations

        foreach ($devises as $sym) {
            $soldes[$sym]  = 0.0;
            $grouped[$sym] = [];
        }

        // 🔹 7. Calcul chronologique (du plus ancien au plus récent)
        foreach ($rows as $ligne) {
            $sym = $ligne['devise'] ?: 'gnf';

            if (! isset($grouped[$sym])) {
                $grouped[$sym] = [];
                $soldes[$sym]  = 0.0;
            }

            $solde_avant = $soldes[$sym];
            $stock_avant = $stockGlobal;

            // 💰 Solde
            $soldes[$sym] += ((float) $ligne['credit'] - (float) $ligne['debit']);

            // ⚖️ Stock global partagé
            if ($ligne['type'] === 'fixing') {
                $stockGlobal -= (float) $ligne['poids_sortie'];
            } elseif ($ligne['type'] === 'livraison') {
                $stockGlobal += (float) $ligne['poids_entree'];
            }

            $ligne['solde_avant']        = round($solde_avant, 2);
            $ligne['solde_apres']        = round($soldes[$sym], 2);
            $ligne['solde_apres_fixing'] = round($soldes[$sym], 2);
            $ligne['stock_avant']        = round($stock_avant, 3);
            $ligne['stock_apres']        = round($stockGlobal, 3);

            $grouped[$sym][] = $ligne;
        }

        // 🔹 8. Aucun tri supplémentaire : on garde l’ordre naturel (ASC)
        // Les lignes sont déjà triées du plus ancien au plus récent

        return [
            'status'                 => 200,
            'message'                => 'Relevé combiné généré avec succès.',
            'operations_financieres' => (object) $grouped,
            'stock_final'            => round($stockGlobal, 3),
        ];
    }

    public function calculerStockClient(int $id_client): array
    {
        $livraisonIds = InitLivraison::where('id_client', $id_client)->pluck('id');

        // 🔹 Total livré (pureté réelle)
        $totalLivre = Fondation::whereHas('expedition', function ($q) use ($livraisonIds) {
            $q->whereIn('id_init_livraison', $livraisonIds);
        })
            ->get()
            ->sum(function ($fondation) {

                $poids = ($fondation->poids_dubai > 0)
                    ? (float) $fondation->poids_dubai
                    : (float) $fondation->poids_fondu;

                $carrat = ($fondation->carrat_dubai > 0)
                    ? (float) $fondation->carrat_dubai
                    : (float) $fondation->carrat_fondu;

                return round(($poids * $carrat) / 24, 2);
            });

        // 🔹 Total des fixings vendus
        $totalFixing = FixingClient::where('id_client', $id_client)
            ->where('status', 'vendu')
            ->sum('poids_pro');

        // ❗ NE PAS bloquer à 0 → le stock peut être NEGATIF
        $resteStock = $totalLivre - $totalFixing;

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
            'devise',
            'clients',
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

    //     // ✅ Fusion des opérations et fixings
    //     $data = $operationsClient
    //         ->concat($fixings)
    //         ->sortBy('date') // plus ancien → plus récent
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

    //     // ✅ Retour structuré
    //     return [
    //         'usd' => $usdList,
    //         'gnf' => $gnfList,
    //     ];
    // }
    public function getReleveClientParPeriode(int $id_client, string $date_debut, string $date_fin): array
    {
        // ============================
        // 💰 PARTIE 1 : Opérations financières
        // ============================
        $operationsClient = OperationClient::with(['typeOperation', 'devise', 'compte.banque'])
            ->where('id_client', $id_client)
            ->whereBetween('created_at', [$date_debut, $date_fin])
            ->get()
            ->map(function ($op) {
                $nature = $op->typeOperation?->nature; // 1 = entrée, 0 = sortie
                return [
                    'date'          => $op->created_at?->format('Y-m-d H:i:s'),
                    'reference'     => $op->reference,
                    'type'          => 'operation_client',
                    'libelle'       => $op->typeOperation?->libelle ?? 'Opération client',
                    'banque'        => $op->compte?->banque?->libelle ?? null,
                    'numero_compte' => $op->compte?->numero_compte ?? null,
                    'devise'        => strtolower($op->devise?->symbole ?? ''),
                    'debit'         => $nature == 0 ? (float) $op->montant : 0,
                    'credit'        => $nature == 1 ? (float) $op->montant : 0,
                ];
            });

        // 🔹 Calcul des soldes financiers par devise
        $soldesFinanciers    = [];
        $resultatsFinanciers = [];

        foreach ($operationsClient as $ligne) {
            $symbole = $ligne['devise'] ?: 'gnf';
            if (! isset($soldesFinanciers[$symbole])) {
                $soldesFinanciers[$symbole]    = 0;
                $resultatsFinanciers[$symbole] = [];
            }

            $soldesFinanciers[$symbole] += $ligne['credit'] - $ligne['debit'];
            $ligne['solde_apres']            = round($soldesFinanciers[$symbole], 2);
            $resultatsFinanciers[$symbole][] = $ligne;
        }

        // ============================
        // 🟡 PARTIE 2 : Fixings (ventes d’or)
        // ============================
        $fixings = FixingClient::with(['devise'])
            ->where('id_client', $id_client)
            ->where('status', 'vendu')
            ->whereBetween('created_at', [$date_debut, $date_fin])
            ->get()
            ->map(function ($fix) {
                $calcul = app(FixingClientService::class)->calculerFacture($fix->id);
                return [
                    'date'      => $fix->created_at?->format('Y-m-d H:i:s'),
                    'reference' => $fix->reference ?? null,
                    'type'      => 'fixing',
                    'libelle'   => "Vente or : {$calcul['poids_total']} g à {$calcul['prix_unitaire']} /g",
                    'bourse'        => $calcul['bourse'] ?? 0,
                    'discompte'     => $calcul['discompte'] ?? 0,
                    'prix_unitaire' => $calcul['prix_unitaire'] ?? 0,
                    'poids_total'   => $calcul['poids_total'] ?? 0,
                    'total_facture' => $calcul['total_facture'] ?? 0,
                    'devise'        => strtolower($fix->devise?->symbole ?? ''),
                ];
            });

        // 🔹 Calcul du stock d’or chronologique
        $poidsActuel = 0;
        $resultatsOr = [];

        foreach ($fixings as $ligne) {
            $poidsActuel -= $ligne['poids_total'];
            $ligne['poids_apres'] = round($poidsActuel, 3);
            $resultatsOr[]        = $ligne;
        }

        // 🔹 Inversion (plus récent → plus ancien)
        foreach ($resultatsFinanciers as $symbole => &$list) {
            $list = array_reverse($list);
        }
        $resultatsOr = array_reverse($resultatsOr);

        // ============================
        // ✅ Structure de sortie finale
        // ============================
        return [
            'status'  => 200,
            'message' => "Relevé du client entre {$date_debut} et {$date_fin} généré avec succès.",
            'operations_financieres' => $resultatsFinanciers,
            'operations_or' => $resultatsOr,
        ];
    }

    public function calculerSoldeGlobalClients(): array
    {
        $totauxSoldes = [];
        $totauxFlux   = [];

        // 🔹 Parcours de tous les clients
        foreach (Client::all(['id']) as $client) {
            $resultat = app(ClientService::class)->calculerSoldeClient($client->id);

            $soldes = $resultat['soldes'] ?? [];
            $flux   = $resultat['flux'] ?? [];

            // 🔹 Agrégation des soldes (format : [{devise, montant}])
            foreach ($soldes as $item) {
                $devise  = $item['devise'];
                $montant = $item['montant'];

                if (! isset($totauxSoldes[$devise])) {
                    $totauxSoldes[$devise] = 0;
                }
                $totauxSoldes[$devise] += $montant;
            }

            // 🔹 Agrégation des flux (format : [{devise, entrees, sorties}])
            foreach ($flux as $item) {
                $devise  = $item['devise'];
                $entrees = $item['entrees'];
                $sorties = $item['sorties'];

                if (! isset($totauxFlux[$devise])) {
                    $totauxFlux[$devise] = [
                        'entrees' => 0,
                        'sorties' => 0,
                    ];
                }

                $totauxFlux[$devise]['entrees'] += $entrees;
                $totauxFlux[$devise]['sorties'] += $sorties;
            }
        }

        // 🔹 Conversion en tableaux uniformes
        $soldesArray = [];
        foreach ($totauxSoldes as $devise => $montant) {
            $soldesArray[] = [
                'devise'  => $devise,
                'montant' => round($montant, 2),
            ];
        }

        $fluxArray = [];
        foreach ($totauxFlux as $devise => $data) {
            $fluxArray[] = [
                'devise'  => $devise,
                'entrees' => round($data['entrees'], 2),
                'sorties' => round($data['sorties'], 2),
            ];
        }

        // ✅ Résultat final unifié
        return [
            'soldes' => $soldesArray,
            'flux'   => $fluxArray,
        ];
    }

    function deuxChiffresApresVirgule($nombre)
    {
        return floor($nombre * 100) / 100;
    }

}

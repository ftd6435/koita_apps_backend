<?php

namespace App\Modules\Settings\Services;

use App\Modules\Comptabilite\Models\OperationClient;
use App\Modules\Fixing\Models\FixingClient;
use App\Modules\Fixing\Models\InitLivraison;
use App\Modules\Fixing\Services\FixingClientService;
use App\Modules\Settings\Models\Client;
use App\Modules\Settings\Resources\ClientResource;
use App\Modules\Settings\Resources\LivraisonNonFixeeResource;
use Illuminate\Support\Facades\Auth;
use Exception;

class ClientService
{
    /**
     * 🔹 Créer un nouveau client
     */
    public function store(array $data)
    {
        try {
            $data['created_by'] = Auth::id();

            // ✅ S'assurer qu'on récupère un vrai modèle (pas un array)
            $client = Client::create($data);
            $client->refresh();

            return response()->json([
                'status'  => 200,
                'message' => 'Client créé avec succès.',
                'data'    => new ClientResource(
                    $client->load(['createur', 'modificateur', 'initLivraisons', 'fixings'])
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
            $client = Client::findOrFail($id);
            $data['modify_by'] = Auth::id();
            $client->update($data);
            $client->refresh();

            return response()->json([
                'status'  => 200,
                'message' => 'Client mis à jour avec succès.',
                'data'    => new ClientResource(
                    $client->load(['createur', 'modificateur', 'initLivraisons', 'fixings'])
                ),
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
            $client = Client::findOrFail($id);
            $client->delete();

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
     * 🔹 Récupérer tous les clients avec leurs livraisons et fixings
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
    public function calculerSoldeClient(int $id_client): array
    {
        $getTotalParDevise = function (string $deviseSymbole, int $nature) use ($id_client) {
            return OperationClient::where('id_client', $id_client)
                ->whereHas('typeOperation', fn($q) => $q->where('nature', $nature)) // 1=entrée
                ->whereHas('devise', fn($q) => $q->where('symbole', $deviseSymbole))
                ->sum('montant');
        };

        $entreesUSD = $getTotalParDevise('USD', 1);
        $entreesGNF = $getTotalParDevise('GNF', 1);

        $fixings = FixingClient::where('id_client', $id_client)
            ->with('devise')
            ->get();

        $sortiesUSD = 0;
        $sortiesGNF = 0;

        foreach ($fixings as $fixing) {
            $calcul = app(FixingClientService::class)->calculerFacture($fixing->id);
            $montant = $calcul['total_facture'] ?? 0;

            if ($fixing->devise?->symbole === 'USD') {
                $sortiesUSD += $montant;
            } elseif ($fixing->devise?->symbole === 'GNF') {
                $sortiesGNF += $montant;
            }
        }

        return [
            'solde_usd' => round($entreesUSD - $sortiesUSD, 2),
            'solde_gnf' => round($entreesGNF - $sortiesGNF, 2),
        ];
    }

    /**
     * 🔹 Relevé complet (Fixings + Opérations)
     */
    public function getReleveClient(int $id_client): array
    {
        $operations = OperationClient::with(['typeOperation', 'devise'])
            ->where('id_client', $id_client)
            ->get()
            ->map(function ($op) {
                $nature = $op->typeOperation?->nature; // 1=entrée, 2=sortie
                return [
                    'date'    => $op->created_at?->format('Y-m-d H:i:s'),
                    'type'    => 'operation_client',
                    'libelle' => $op->typeOperation?->libelle ?? 'Opération client',
                    'devise'  => $op->devise?->symbole ?? '',
                    'debit'   => $nature == 2 ? (float) $op->montant : 0,
                    'credit'  => $nature == 1 ? (float) $op->montant : 0,
                ];
            });

        $fixings = FixingClient::with(['devise'])
            ->where('id_client', $id_client)
            ->get()
            ->map(function ($fix) {
                $calcul = app(FixingClientService::class)->calculerFacture($fix->id);

                return [
                    'date'    => $fix->created_at?->format('Y-m-d H:i:s'),
                    'type'    => 'fixing',
                    'libelle' => 'Fixing #' . $fix->id,
                    'devise'  => $fix->devise?->symbole ?? '',
                    'debit'   => (float) ($calcul['total_facture'] ?? 0),
                    'credit'  => 0,
                ];
            });

        $operationsComplet = $operations
            ->merge($fixings)
            ->sortBy('date')
            ->values();

        $soldeUSD = 0;
        $soldeGNF = 0;

        $operationsComplet = $operationsComplet->map(function ($op) use (&$soldeUSD, &$soldeGNF) {
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
        });

        return $operationsComplet->toArray();
    }
}

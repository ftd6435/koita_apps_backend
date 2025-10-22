<?php
namespace App\Modules\Settings\Services;

use App\Modules\Comptabilite\Models\OperationClient;
use App\Modules\Fixing\Models\FixingClient;
use App\Modules\Fixing\Models\InitLivraison;
use App\Modules\Fixing\Services\FixingClientService;
use App\Modules\Settings\Models\Client;
use App\Modules\Settings\Resources\ClientResource;
use App\Modules\Settings\Resources\LivraisonNonFixeeResource;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

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

        $fixings = FixingClient::with('devise')->where('id_client', $id_client)->get();

        $sortiesUSD = 0;
        $sortiesGNF = 0;

        foreach ($fixings as $fixing) {
            $calcul  = app(FixingClientService::class)->calculerFacture($fixing->id);
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
        // ✅ Récupération des opérations client
        $operations = OperationClient::with(['typeOperation', 'devise'])
            ->where('id_client', $id_client)
            ->get()
            ->map(function ($op) {
                $nature = $op->typeOperation?->nature; // 1 = entrée, 0 = sortie

                return collect([
                    'date'           => $op->created_at?->format('Y-m-d H:i:s'),
                    'date_operation' => $op->date_operation,
                    'reference'      => $op->reference,
                    'type'           => 'operation_client',
                    'libelle'        => $op->typeOperation?->libelle ?? 'Opération client',
                    'devise'         => $op->devise?->symbole ?? '',
                    'debit'          => $nature == 0 ? (float) $op->montant : 0, // sortie
                    'credit'         => $nature == 1 ? (float) $op->montant : 0, // entrée
                ]);
            });

        // ✅ Récupération des fixings (sorties)
        $fixings = FixingClient::with(['devise'])
            ->where('id_client', $id_client)
            ->get()
            ->map(function ($fix) {
                $calcul = app(FixingClientService::class)->calculerFacture($fix->id);

                $poidsTotal   = $calcul['purete_totale'] ?? 0;
                $montantTotal = $calcul['total_facture'] ?? 0;

                return collect([
                    'date'           => $fix->created_at?->format('Y-m-d H:i:s'),
                    'date_operation' => null,
                    'reference'      => $fix->reference ?? null,
                    'type'           => 'fixing',
                    'libelle'        => "Facturation du {$poidsTotal} g",
                    'devise' => $fix->devise?->symbole ?? '',
                    'debit'  => (float) $montantTotal,
                    'credit' => 0,
                ]);
            });

        // ✅ Fusion complète et triée
        $operationsComplet = $operations
            ->concat($fixings)
            ->sortBy('date')
            ->values();

        // ✅ Calcul des soldes progressifs
        $soldeUSD = 0;
        $soldeGNF = 0;

        $operationsComplet = $operationsComplet->map(function ($op) use (&$soldeUSD, &$soldeGNF) {
            $devise = $op['devise'] ?? '';

            if ($devise === 'USD') {
                $soldeUSD += $op['credit'] - $op['debit'];
                $op['solde_apres'] = round($soldeUSD, 2);
            } elseif ($devise === 'GNF') {
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

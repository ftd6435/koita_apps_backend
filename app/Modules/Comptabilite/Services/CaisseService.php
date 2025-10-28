<?php
namespace App\Modules\Comptabilite\Services;

use App\Modules\Comptabilite\Models\Caisse;
use App\Modules\Comptabilite\Models\TypeOperation;
use App\Modules\Comptabilite\Resources\CaisseResource;
use App\Modules\Settings\Models\Client;
use App\Modules\Settings\Models\Devise;
use App\Modules\Settings\Models\Divers;
use App\Modules\Settings\Services\ClientService;
use App\Modules\Settings\Services\DiversService;
use App\Traits\Helper;
use Exception;
use Illuminate\Support\Facades\Auth;

class CaisseService
{
    /**
     * 🔹 Enregistrer une nouvelle opération de caisse
     */

    use Helper;
    public function store(array $data)
    {
        try {
            // Charger l'opération et sa nature (entrée ou sortie)
            $typeOperation = TypeOperation::find($data['id_type_operation']);

            // 🔸 Si c’est une sortie (décaissement), vérifier le solde disponible
            if ($typeOperation->nature === 0) {
                $devise = Devise::find($data['id_devise']);

                // Calcul du solde actuel (entrées - sorties) pour cette devise
                $entrees = Caisse::whereHas('typeOperation', function ($q) {
                    $q->where('nature', 'entree');
                })
                    ->where('id_devise', $data['id_devise'])
                    ->sum('montant');

                $sorties = Caisse::whereHas('typeOperation', function ($q) {
                    $q->where('nature', 'sortie');
                })
                    ->where('id_devise', $data['id_devise'])
                    ->sum('montant');

                $soldeDisponible = $entrees - $sorties;

                // Vérification du solde avant décaissement
                if ($soldeDisponible < $data['montant']) {
                    return response()->json([
                        'status'  => 400,
                        'message' => "Solde insuffisant pour effectuer ce décaissement.",
                        'data'    => [
                            'solde_disponible' => round($soldeDisponible, 2),
                            'montant_demande'  => round($data['montant'], 2),
                            'devise'           => $devise->symbole ?? '',
                        ],
                    ]);
                }
            }

            // ✅ Si tout est bon, on enregistre l’opération
            $data['created_by'] = Auth::id();
            $caisse             = Caisse::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Opération de caisse enregistrée avec succès.',
                'data'    => new CaisseResource(
                    $caisse->load(['devise', 'typeOperation', 'createur'])
                ),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l’enregistrement de la caisse.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Liste complète des opérations de caisse
     */
    public function getAll()
    {
        try {
            $caisses = Caisse::with(['devise', 'typeOperation', 'createur'])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status'  => 200,
                'message' => 'Liste des opérations de caisse récupérée avec succès.',
                'data'    => [

                    'operations'  => CaisseResource::collection($caisses),
                    'soldeGlobal' => $this->calculerSoldeGlobal(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des opérations de caisse.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Supprimer une opération de caisse
     */
    public function delete(int $id)
    {
        try {
            $caisse = Caisse::findOrFail($id);
            $caisse->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Opération de caisse supprimée avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de l’opération de caisse.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function calculerSoldeCaisse(): array
    {
        // ✅ Flux entrée USD
        $entreesUSD = Caisse::whereHas('devise', fn($q) => $q->where('symbole', 'USD'))
            ->whereHas('typeOperation', fn($q) => $q->where('nature', 1))
            ->sum('montant');

        // ✅ Flux sortie USD
        $sortiesUSD = Caisse::whereHas('devise', fn($q) => $q->where('symbole', 'USD'))
            ->whereHas('typeOperation', fn($q) => $q->where('nature', 0))
            ->sum('montant');

        // ✅ Flux entrée GNF
        $entreesGNF = Caisse::whereHas('devise', fn($q) => $q->where('symbole', 'GNF'))
            ->whereHas('typeOperation', fn($q) => $q->where('nature', 1))
            ->sum('montant');

        // ✅ Flux sortie GNF
        $sortiesGNF = Caisse::whereHas('devise', fn($q) => $q->where('symbole', 'GNF'))
            ->whereHas('typeOperation', fn($q) => $q->where('nature', 0))
            ->sum('montant');

        // ✅ Solde Caisse
        $soldeUSD = $entreesUSD - $sortiesUSD;
        $soldeGNF = $entreesGNF - $sortiesGNF;

        return [
            // ✅ Solde final
            'solde_usd'   => round($soldeUSD, 2),
            'solde_gnf'   => round($soldeGNF, 2),

            // ✅ Ajout des flux
            'entrees_usd' => round($entreesUSD, 2),
            'sorties_usd' => round($sortiesUSD, 2),
            'entrees_gnf' => round($entreesGNF, 2),
            'sorties_gnf' => round($sortiesGNF, 2),
        ];
    }

    public function calculerSoldeGlobal(): array
    {
        // ✅ Solde Caisse
        $soldeCaisse = $this->calculerSoldeCaisse();

        $total_usd = $soldeCaisse['solde_usd'];
        $total_gnf = $soldeCaisse['solde_gnf'];

        // ✅ Flux global = flux CAISSE uniquement AU DÉPART
        $entrees_usd = $soldeCaisse['entrees_usd'];
        $sorties_usd = $soldeCaisse['sorties_usd'];
        $entrees_gnf = $soldeCaisse['entrees_gnf'];
        $sorties_gnf = $soldeCaisse['sorties_gnf'];

        // ✅ Clients
        $soldeClientsUSD  = 0;
        $soldeClientsGNF  = 0;
        $entreeClientsUSD = 0;
        $entreeClientsGNF = 0;
        $sortieClientsUSD = 0;
        $sortieClientsGNF = 0;

        foreach (Client::all(['id']) as $client) {
            $s = app(ClientService::class)->calculerSoldeClient($client->id);

            $soldeClientsUSD += $s['solde_usd'];
            $soldeClientsGNF += $s['solde_gnf'];

            // ✅ Ajout flux CLIENTS au flux GLOBAL
            $entreeClientsUSD += $s['entrees_usd'];
            $entreeClientsGNF += $s['entrees_gnf'];
            $sortieClientsUSD += $s['sorties_usd'];
            $sortieClientsGNF += $s['sorties_gnf'];

            $entrees_usd += $s['entrees_usd'];
            $sorties_usd += $s['sorties_usd'];
            $entrees_gnf += $s['entrees_gnf'];
            $sorties_gnf += $s['sorties_gnf'];

            $total_usd += $s['solde_usd'];
            $total_gnf += $s['solde_gnf'];
        }

        // ✅ Divers
        $soldeDiversUSD  = 0;
        $soldeDiversGNF  = 0;
        $entreeDiversUSD = 0;
        $entreeDiversGNF = 0;
        $sortieDiversUSD = 0;
        $sortieDiversGNF = 0;

        foreach (Divers::all(['id']) as $divers) {
            $s = app(DiversService::class)->calculerSoldeDivers($divers->id);

            $soldeDiversUSD += $s['usd'];
            $soldeDiversGNF += $s['gnf'];

            // ✅ Ajout flux DIVERS au flux GLOBAL
            $entreeDiversUSD += $s['entrees_usd'];
            $entreeDiversGNF += $s['entrees_gnf'];
            $sortieDiversUSD += $s['sorties_usd'];
            $sortieDiversGNF += $s['sorties_gnf'];

            $entrees_usd += $s['entrees_usd'];
            $sorties_usd += $s['sorties_usd'];
            $entrees_gnf += $s['entrees_gnf'];
            $sorties_gnf += $s['sorties_gnf'];

            $total_usd += $s['usd'];
            $total_gnf += $s['gnf'];
        }

        return [
            'solde_usd'   => round($total_usd, 2),
            'solde_gnf'   => round($total_gnf, 2),

            // ✅ Flux global CAISSE + CLIENTS + DIVERS
            'entrees_usd' => round($entrees_usd, 2),
            'sorties_usd' => round($sorties_usd, 2),
            'entrees_gnf' => round($entrees_gnf, 2),
            'sorties_gnf' => round($sorties_gnf, 2),

            'details'     => [
                'caisse'  => [
                    'solde_usd'   => $soldeCaisse['solde_usd'],
                    'solde_gnf'   => $soldeCaisse['solde_gnf'],
                    'entrees_usd' => $soldeCaisse['entrees_usd'],
                    'sorties_usd' => $soldeCaisse['sorties_usd'],
                    'entrees_gnf' => $soldeCaisse['entrees_gnf'],
                    'sorties_gnf' => $soldeCaisse['sorties_gnf'],
                ],
                'clients' => [
                    'solde_usd'   => round($soldeClientsUSD, 2),
                    'solde_gnf'   => round($soldeClientsGNF, 2),
                    'entrees_usd' => round($entreeClientsUSD, 2),
                    'sorties_usd' => round($sortieClientsUSD, 2),
                    'entrees_gnf' => round($entreeClientsGNF, 2),
                    'sorties_gnf' => round($sortieClientsGNF, 2),
                ],
                'divers'  => [
                    'solde_usd'   => round($soldeDiversUSD, 2),
                    'solde_gnf'   => round($soldeDiversGNF, 2),
                    'entrees_usd' => round($entreeDiversUSD, 2),
                    'sorties_usd' => round($sortieDiversUSD, 2),
                    'entrees_gnf' => round($entreeDiversGNF, 2),
                    'sorties_gnf' => round($sortieDiversGNF, 2),
                ],
            ],
        ];
    }

}

<?php
namespace App\Modules\Comptabilite\Services;

use App\Modules\Administration\Models\Fournisseur;
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
                'data'    => CaisseResource::collection($caisses),
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
        // ✅ Appel direct au solde de la caisse
        $soldeCaisse    = $this->calculerSoldeCaisse();
        $soldeCaisseUSD = $soldeCaisse['solde_usd'] ?? 0;
        $soldeCaisseGNF = $soldeCaisse['solde_gnf'] ?? 0;

        // ✅ Solde Clients
        $soldeClientUSD = 0;
        $soldeClientGNF = 0;

        foreach (Client::all() as $client) {
            $s = app(ClientService::class)->calculerSoldeClient($client->id);
            $soldeClientUSD += $s['solde_usd'];
            $soldeClientGNF += $s['solde_gnf'];
        }

        // ✅ Solde Divers
        $soldeDiversUSD = 0;
        $soldeDiversGNF = 0;

        foreach (Divers::all() as $divers) {
            $s = app(DiversService::class)->calculerSoldeDivers($divers->id);
            $soldeDiversUSD += $s['usd'] ?? 0;
            $soldeDiversGNF += $s['gnf'] ?? 0;
        }

        // ✅ Solde Fournisseurs
        $soldeFournisseurUSD = 0;
        $soldeFournisseurGNF = 0;

        foreach (Fournisseur::all() as $f) {
            $s = $this->soldeGlobalFournisseur($f->id);

            foreach ($s as $item) {
                if ($item['symbole'] === 'USD') {
                    $soldeFournisseurUSD += $item['montant'];
                } elseif ($item['symbole'] === 'GNF') {
                    $soldeFournisseurGNF += $item['montant'];
                }
            }
        }

        // ✅ Solde Final
        return [
            'solde_usd' => round($soldeCaisseUSD + $soldeClientUSD + $soldeDiversUSD + $soldeFournisseurUSD, 2),
            'solde_gnf' => round($soldeCaisseGNF + $soldeClientGNF + $soldeDiversGNF + $soldeFournisseurGNF, 2),
        ];
    }

}

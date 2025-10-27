<?php
namespace App\Modules\Fixing\Services;

use App\Modules\Fixing\Models\FixingClient;
use App\Modules\Fixing\Resources\FixingClientResource;
use App\Modules\Fondation\Models\Fondation;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FixingClientService
{
    /**
     * 🔹 Enregistrer un nouveau Fixing Client
     */
    public function store(array $payload)
    {
        DB::beginTransaction();

        try {
            $payload['created_by'] = Auth::id();

            // ✅ Création du fixing client
            $fixing = FixingClient::create($payload);

            // ✅ Mise à jour des fondations associées (si fournies)
            if (! empty($payload['id_barre_fondu']) && is_array($payload['id_barre_fondu'])) {
                Fondation::whereIn('id', $payload['id_barre_fondu'])
                    ->update(['id_fixing' => $fixing->id]);
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Fixing client créé avec succès.',
                'data'    => new FixingClientResource(
                    $fixing->load(['client', 'devise', 'fondations', 'createur', 'modificateur'])
                ),
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création du fixing client.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Récupérer la liste de tous les fixings clients
     */
    public function getAll()
    {
        try {
            $fixings = FixingClient::with(['client', 'devise', 'fondations', 'createur', 'modificateur'])
                ->orderByDesc('id')
                ->get();

            return response()->json([
                'status'  => 200,
                'message' => 'Liste des fixings clients récupérée avec succès.',
                'data'    => FixingClientResource::collection($fixings),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des fixings clients.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Récupérer un fixing client spécifique
     */
    public function getOne(int $id)
    {
        try {
            $fixing = FixingClient::with(['client', 'devise', 'fondations', 'createur', 'modificateur'])
                ->find($id);

            if (! $fixing) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Fixing client introuvable.',
                ], 404);
            }

            return response()->json([
                'status'  => 200,
                'message' => 'Fixing client récupéré avec succès.',
                'data'    => new FixingClientResource($fixing),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération du fixing client.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(int $id, array $payload)
    {
        DB::beginTransaction();

        try {
            $fixing = FixingClient::find($id);

            if (! $fixing) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Fixing client introuvable.',
                ], 404);
            }

            // 🔹 Mise à jour des champs de base
            $payload['updated_by'] = Auth::id();
            $fixing->update($payload);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Fixing client mis à jour avec succès.',
                'data'    => new FixingClientResource(
                    $fixing->load(['client', 'devise', 'fondations', 'createur', 'modificateur'])
                ),
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour du fixing client.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Supprimer un fixing client
     */
    public function delete(int $id)
    {
        DB::beginTransaction();

        try {
            $fixing = FixingClient::find($id);

            if (! $fixing) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Fixing client introuvable.',
                ], 404);
            }

            $fixing->delete();
            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Fixing client supprimé avec succès.',
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression du fixing client.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function calculerFacture(int $id_fixing): array
    {
        $fixing = FixingClient::with('client')->find($id_fixing);

        if (! $fixing) {
            return [
                'status'  => 404,
                'message' => "Fixing introuvable avec l’ID {$id_fixing}.",
            ];
        }

        // 🔹 Constantes
        $densite    = 22;
        $bourse     = (float) $fixing->bourse;
        $discompte  = (float) $fixing->discompte;
        $typeClient = $fixing->client?->type_client ?? 'local';

        // 🔹 Récupération des fondations liées
        $fondations = Fondation::where('id_fixing', $fixing->id)->get();

        if ($fondations->isEmpty()) {
            return [
                'id_fixing' => $fixing->id,
                'message'   => 'Aucune fondation trouvée pour ce fixing.',
            ];
        }

        // === Étape 1 : Calculs par fondation ===
        $details            = [];
        $poidsTotal         = 0;
        $sommeCaratPonderee = 0;
        $pureteTotale       = 0;

        foreach ($fondations as $fondation) {
            $poids = (float) $fondation->poids_fondu;
            $carat = (float) $fondation->carrat_fondu;

            // 💎 Pureté brute (poids d’or pur)
            $purete = ($poids * $carat) / 24;

            $poidsTotal += $poids;
            $sommeCaratPonderee += $poids * $carat;
            $pureteTotale += $purete;

            $details[] = [
                'id_fondation'  => $fondation->id,
                'reference'     => $fondation->initFondation?->reference ?? null,
                'poids_fondu'   => round($poids, 2),
                'carrat_fondu'  => round($carat, 2),
                'purete'        => round($purete, 2),
                'montant_barre' => 0, // sera défini après le calcul du prix unitaire
            ];
        }

        // === Étape 2 : Calculs globaux ===
        $carratMoyen  = $poidsTotal > 0 ? $sommeCaratPonderee / $poidsTotal : 0;
        $carratMoyen  = round($carratMoyen, 2);
        $pureteTotale = round($pureteTotale, 3);

        // === Étape 3 : Calcul du prix unitaire selon le type de client ===
        if ($typeClient === 'local') {
            $prixUnitaire = ($bourse / 34) - $discompte;
        } else {
            $prixUnitaire = ($bourse / 31.10347) - (32 * $discompte);
        }

        // === Étape 4 : Calcul du montant_barre et total_facture ===
        $totalFacture = 0;
        foreach ($details as &$detail) {
            $detail['montant_barre'] = round($detail['purete'] * $prixUnitaire, 2);
            $totalFacture += $detail['montant_barre'];
        }
        unset($detail); // bonne pratique

        // === Étape 5 : Arrondis et retour final ===
        $prixUnitaireTronque = $prixUnitaire ? $this->truncate($prixUnitaire, 2) : null;
        $totalFactureTronque = round($totalFacture, 2);

        return [
            'id_fixing'     => $fixing->id,
            'type_client'   => $typeClient,
            'prix_unitaire' => $prixUnitaireTronque,
            'poids_total'   => round($poidsTotal, 2),
            'carrat_moyen'  => $carratMoyen,
            'purete_totale' => $pureteTotale,
            'fondations'    => $details,
            'total_facture' => $totalFactureTronque,
        ];
    }

/**
 * 🔹 Tronque une valeur sans arrondir (utile pour les montants financiers).
 */
    private function truncate(float $value, int $decimals = 2): float
    {
        $factor = pow(10, $decimals);
        return floor($value * $factor) / $factor;
    }

    public function statistiquesFixing()
    {
        try {
            $stats = FixingClient::selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            return response()->json([
                'status'  => 200,
                'message' => 'Statistiques des fixings récupérées avec succès.',
                'data'    => [
                    'en_attente' => $stats['en attente'] ?? 0,
                    'confirmer'  => $stats['confirmer'] ?? 0,
                    'valider'    => $stats['valider'] ?? 0,
                ],
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des statistiques.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}

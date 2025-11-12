<?php

namespace App\Modules\Fixing\Services;

use App\Modules\Comptabilite\Models\OperationClient;
use App\Modules\Comptabilite\Models\OperationDivers;
use App\Modules\Fixing\Models\FixingClient;
use App\Modules\Fixing\Resources\FixingClientResource;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FixingClientService
{
    /**
     * ➕ Créer un nouveau fixing client
     */
    public function store(array $payload)
    {
        DB::beginTransaction();

        try {
            $payload['created_by'] = Auth::id();

            // ✅ Création du fixing client
            $fixing = FixingClient::create($payload);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Fixing client créé avec succès.',
                
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
     * 📋 Récupérer tous les fixings clients
     */
    public function getAll()
    {
        try {
            $fixings = FixingClient::with(['client', 'devise', 'createur', 'modificateur'])
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
     * 🔍 Détails d’un fixing spécifique
     */
    public function getOne(int $id)
    {
        try {
            $fixing = FixingClient::with(['client', 'devise', 'createur', 'modificateur'])
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

    /**
     * ✏️ Mettre à jour un fixing client
     */
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

            $payload['updated_by'] = Auth::id();
            $fixing->update($payload);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Fixing client mis à jour avec succès.',
               
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
     * 🗑️ Supprimer un fixing client
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

    /**
     * 📊 Calculer la facture d’un fixing
     */
   public function calculerFacture(int $id_fixing): array
{
    $fixing = FixingClient::with('client')->find($id_fixing);

    if (! $fixing) {
        return [
            'status'  => 404,
            'message' => "Fixing introuvable avec l’ID {$id_fixing}.",
        ];
    }

    $poidsPro   = (float) $fixing->poids_pro;
    $bourse     = (float) $fixing->bourse;
    $prixUnitaire     = (float) $fixing->prix_unitaire;
    $discompte  = (float) ($fixing->discompte ?? 0);
    $typeClient = $fixing->client?->type_client ?? 'local';

    // 💎 Pureté (ici on ne prend plus le carat)
    $pureteTotale = $poidsPro ;

    // 💰 Si le fixing est vendu → calcul normal
    if ($fixing->status === 'vendu') {
       

        $totalFacture = $pureteTotale * $prixUnitaire;
    } 
    // ⚠️ Sinon (provisoire) → pas de calcul, mais on garde les mêmes clés
    else {
        $prixUnitaire = 0;
        $totalFacture = 0;
    }

    return [
        'status'        => 200,
        'id_fixing'     => $fixing->id,
        'status_fixing' => $fixing->status,
        'type_client'   => $typeClient,
        'poids_total'   => round($poidsPro, 2),
        'bourse'        => round($bourse, 2),
        'discompte'     => round($discompte, 2),
        'purete_totale' => round($pureteTotale, 2),
        'prix_unitaire' => round($prixUnitaire, 2),
        'total_facture' => round($totalFacture, 2),
    ];
}


    /**
     * ⚙️ Tronquer un nombre sans arrondir
     */
   

    /**
     * 📈 Statistiques globales des fixings
     */
    public function statistiquesFixing()
    {
        try {
            $stats = FixingClient::selectRaw('LOWER(status) as status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            return response()->json([
                'status'  => 200,
                'message' => 'Statistiques récupérées avec succès.',
                'data'    => [
                    'provisoires' => $stats['provisoire'] ?? 0,
                    'vendus'      => $stats['vendu'] ?? 0,
                    'par_semaine' => $this->fixingsClientSemaine(),
                    'activites'   => $this->dernieresActivites(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des statistiques.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 📅 Statistiques de la semaine
     */
    public function fixingsClientSemaine(): array
    {
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $today = Carbon::today();
        $result = [];

        for ($i = 0; $i < 7; $i++) {
            $jour = $today->startOfWeek()->addDays($i);
            $fixings = FixingClient::whereDate('created_at', $jour)->get();

            $total = 0;
            foreach ($fixings as $fixing) {
                $calcul = $this->calculerFacture($fixing->id);
                $total += (float) ($calcul['total_facture'] ?? 0);
            }

            $result[] = [
                'jour'  => $jours[$i],
                'total' => round($total, 2),
                'date'  => $jour->format('Y-m-d'),
            ];
        }

        return $result;
    }

    /**
     * 🕒 Dernières activités
     */
    public function dernieresActivites(): array
    {
        $activites = collect([]);

        FixingClient::latest()->take(5)->get(['id', 'status', 'created_at'])->each(function ($item) use (&$activites) {
            $activites->push([
                'type' => 'fixing_client',
                'id'   => $item->id,
                'info' => $item->status,
                'date' => $item->created_at,
            ]);
        });

        return $activites
            ->sortByDesc('date')
            ->values()
            ->take(5)
            ->map(fn($a) => [
                'type' => $a['type'],
                'id'   => $a['id'],
                'info' => $a['info'],
                'date' => $a['date']->format('Y-m-d H:i'),
            ])
            ->toArray();
    }
}

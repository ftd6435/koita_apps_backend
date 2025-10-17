<?php

namespace App\Modules\Fixing\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Settings\Resources\ClientResource;
use App\Modules\Settings\Resources\DeviseResource;
use App\Modules\Fondation\Resources\FondationResource;
use App\Modules\Fixing\Services\FixingClientService;

class FixingClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // 🔹 Récupération du calcul complet depuis le service
        $calculs = app(FixingClientService::class)->calculerFacture($this->id);

        return [
            'id'            => $this->id,
            'poids_pro'     => (float) $this->poids_pro,
            'carrat_moyen'  => (float) $this->carrat_moyen,
            'discompte'     => (float) $this->discompte,
            'bourse'        => (float) $this->bourse,
            'prix_unitaire' => (float) ($calculs['prix_unitaire'] ?? 0),
            'status'        => $this->status ?? 'en attente',

            // 🔹 Relations principales
            'client'        => new ClientResource($this->whenLoaded('client')),
            'devise'        => new DeviseResource($this->whenLoaded('devise')),

            // 🔹 Fondations liées à ce fixing client
            'fondations'    => FondationResource::collection(
                $this->whenLoaded('fondations')
            ),

            // 🔹 Données calculées
            'calculs' => [
                'prix_unitaire' => $calculs['prix_unitaire'] ?? 0,
                'poids_total'   => $calculs['poids_total'] ?? 0,
                'carrat_moyen'  => $calculs['carrat_moyen'] ?? 0,
                'total_facture' => $calculs['total_facture'] ?? 0,
                'details'       => $calculs['fondations'] ?? [],
            ],

            // 🔹 Audit
            'created_by'    => $this->createur?->name,
            'updated_by'    => $this->modificateur?->name,

            // 🔹 Dates formatées
            'created_at'    => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'    => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

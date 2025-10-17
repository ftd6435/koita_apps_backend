<?php

namespace App\Modules\Fixing\Resources;

use App\Modules\Fixing\Services\ExpeditionService;
use App\Modules\Settings\Resources\ClientResource;
use App\Modules\Fondation\Resources\FondationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InitLivraisonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Calcul poids + carat
        $calculs = app(ExpeditionService::class)
            ->calculerPoidsEtCarat($this->id);

        return [
            'id'           => $this->id,
            'reference'    => $this->reference ?? '',
            'commentaire'  => $this->commentaire ?? '',
            'status'       => $this->status ?? 'encours',

            // Client
            'client'       => new ClientResource($this->whenLoaded('client')),

            // Fondations liées directement
            'fondations'   => FondationResource::collection(
                $this->whenLoaded('fondations')
            ),

            // Valeurs calculées
            'poids_total'  => $calculs['poids_total'],
            'carrat_moyen' => $calculs['carrat_moyen'],

            // Audit
            'created_by'   => $this->createur?->name,
            'modify_by'    => $this->modificateur?->name,

            // Dates
            'created_at'   => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'   => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

<?php

namespace App\Modules\Fixing\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Settings\Resources\ClientResource;
use App\Modules\Fixing\Resources\ExpeditionResource;

class InitLivraisonResource extends JsonResource
{
    /**
     * 🔹 Transforme la ressource en tableau JSON
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'reference'   => $this->reference ?? '',
            'commentaire' => $this->commentaire ?? '',
            'status'      => $this->status ?? 'encours',

            // 🔹 Client associé (via ClientResource)
            'client'      => new ClientResource($this->whenLoaded('client')),

            // 🔹 Liste des expéditions liées
            'expeditions' => ExpeditionResource::collection(
                $this->whenLoaded('expeditions')
            ),

            // 🔹 Audit
            'created_by'  => $this->createur?->name,
            'modify_by'   => $this->modificateur?->name,

            // 🔹 Dates formatées
            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'  => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

<?php

namespace App\Modules\Fixing\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Fondation\Resources\FondationResource;
use App\Modules\Fixing\Resources\InitLivraisonResource;

class ExpeditionResource extends JsonResource
{
    /**
     * Transforme la ressource en tableau JSON.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'id_barre_fondu'    => $this->id_barre_fondu,
            'id_init_livraison' => $this->id_init_livraison,

            // 🔹 Fondation associée
            'fondation'         => new FondationResource($this->whenLoaded('fondation')),

            // 🔹 Livraison liée
            'init_livraison'    => new InitLivraisonResource($this->whenLoaded('initLivraison')),

            // 🔹 Audit
            'created_by'        => $this->createur?->name,
            'modify_by'         => $this->modificateur?->name,

            // 🔹 Dates
            'created_at'        => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'        => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

<?php

namespace App\Modules\Fondation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Fondation\Resources\FondationResource;

class InitFondationResource extends JsonResource
{
    /**
     * Transforme la ressource en tableau JSON.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'reference'  => $this->reference,
            // 🔹 Liste des fondations associées
            'fondations' => FondationResource::collection($this->whenLoaded('fondations')),
            
            // 🔹 Audit
            'created_by' => $this->createur?->name,
            'modify_by'  => $this->modificateur?->name,

            // 🔹 Dates
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

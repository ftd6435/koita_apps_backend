<?php

namespace App\Modules\Comptabilite\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Settings\Resources\DeviseResource;
use App\Modules\Comptabilite\Resources\TypeOperationResource;
use App\Modules\Settings\Resources\DiversResource;

class OperationDiversResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return array_filter([
            'id'               => $this->id,
            'reference'        => $this->reference ?? null,
            'date_operation'   => $this->date_operation 
                                    ? $this->date_operation->format('Y-m-d H:i:s') 
                                    : $this->created_at?->format('Y-m-d H:i:s'),
            'montant'          => (float) $this->montant,
            'commentaire'      => $this->commentaire,

            // 🔹 Relations principales
            'type_operation'   => new TypeOperationResource($this->whenLoaded('typeOperation')),
            'divers'           => new DiversResource($this->whenLoaded('divers')),
            'devise'           => new DeviseResource($this->whenLoaded('devise')),

            // 🔹 Audit
            'created_by'       => $this->createur?->name,
            'updated_by'       => $this->modificateur?->name,

            // 🔹 Dates
            'created_at'       => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'       => $this->updated_at?->format('Y-m-d H:i:s'),
        ], fn($value) => !is_null($value));
    }
}

<?php

namespace App\Modules\Comptabilite\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Settings\Resources\ClientResource;
use App\Modules\Settings\Resources\DeviseResource;
use App\Modules\Comptabilite\Resources\TypeOperationResource;

class OperationClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // 🕒 Si la date d’opération est nulle, on prend la date de création
        $dateOperation = $this->date_operation
            ? date('Y-m-d', strtotime($this->date_operation))
            : ($this->created_at?->format('Y-m-d'));

        return array_filter([
            'id'               => $this->id,
            'reference'        => $this->reference,
            'date_operation'   => $dateOperation,
            'montant'          => (float) $this->montant,
            'commentaire'      => $this->commentaire,

            // 🔹 Relations principales
            'type_operation'   => new TypeOperationResource($this->whenLoaded('typeOperation')),
            'client'           => new ClientResource($this->whenLoaded('client')),
            'devise'           => new DeviseResource($this->whenLoaded('devise')),

            // 🔹 Audit
            'created_by'       => $this->createur?->name,
            'updated_by'       => $this->modificateur?->name,

            // 🔹 Dates de création et mise à jour
            'created_at'       => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'       => $this->updated_at?->format('Y-m-d H:i:s'),
        ], fn($value) => !is_null($value));
    }
}

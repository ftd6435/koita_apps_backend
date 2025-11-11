<?php

namespace App\Modules\Fixing\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FixingProvisoireResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'poids_pro'     => (float) $this->poids_pro,
            'bourse'        => (float) $this->bourse,
            'status'        => $this->status,
            'devise'        => $this->devise?->symbole ?? null,
            // 🔹 Informations d’audit
            'created_by'      => $this->createur?->name ?? null,
            'date_creation' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}

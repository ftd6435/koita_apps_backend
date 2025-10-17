<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Fondation\Resources\FondationResource;

class LivraisonNonFixeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'reference'  => $this->reference ?? '',
            'commentaire'=> $this->commentaire ?? null,
            'status'     => $this->status ?? 'encours',
            
            // 🔹 Liste des fondations non fixées
            'fondations' => FondationResource::collection(
                $this->fondations->whereNull('id_fixing')
            ),

            // 🔹 Dates formatées
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

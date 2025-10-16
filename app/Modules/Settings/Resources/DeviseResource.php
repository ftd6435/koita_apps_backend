<?php

namespace App\Modules\Settings\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DeviseResource extends JsonResource
{
    /**
     * Transforme la ressource en tableau JSON sans valeurs nulles.
     */
    public function toArray($request): array
    {
        return array_filter([
            'id'             => $this->id,
            'libelle'        => $this->libelle,
            'symbole'        => $this->symbole ?: null,
            'taux_change'    => $this->taux_change ?: null,

            // 🔹 Informations d’audit
            'created_by'     => $this->createur?->name ?: null,
            'modify_by'      => $this->modificateur?->name ?: null,

            // 🔹 Dates formatées
            'created_at'     => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'     => $this->updated_at?->format('Y-m-d H:i:s'),

            // 🔹 Attribut calculé moderne
            'libelle_complet' => $this->libelle_complet,
        ], fn ($value) => !is_null($value));
    }
}

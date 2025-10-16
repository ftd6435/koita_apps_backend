<?php

namespace App\Modules\Settings\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BanqueResource extends JsonResource
{
    /**
     * Transforme la ressource en tableau JSON.
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'nom_banque'    => $this->nom_banque,
            'code_banque'   => $this->code_banque,
            'telephone'     => $this->telephone,
            'adresse'       => $this->adresse,
            'email'         => $this->email,

            // 🔹 Informations d’audit
            'created_by'    => $this->createur?->name ?? null,
            'modify_by'     => $this->modificateur?->name ?? null,

            // 🔹 Dates formatées
            'created_at'    => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'    => $this->updated_at?->format('Y-m-d H:i:s'),

            // 🔹 Attribut calculé (facultatif)
            'nom_complet'   => $this->nom_complet,
        ];
    }
}

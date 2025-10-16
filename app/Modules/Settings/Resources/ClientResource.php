<?php

namespace App\Modules\Settings\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transforme la ressource en tableau JSON sans valeurs nulles.
     */
    public function toArray($request): array
    {
        return array_filter([
            'id'          => $this->id,
            'nom'         => $this->nom,
            'prenom'      => $this->prenom,
            'telephone'   => $this->telephone ?: null,
            'adresse'     => $this->adresse ?: null,
            'email'       => $this->email ?: null,

            // 🔹 Informations d’audit
            'created_by'  => $this->createur?->name ?: null,
            'modify_by'   => $this->modificateur?->name ?: null,

            // 🔹 Dates formatées
            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'  => $this->updated_at?->format('Y-m-d H:i:s'),

            // 🔹 Attribut calculé moderne
            'nom_complet' => $this->nom_complet,
        ], fn ($value) => !is_null($value));
    }
}

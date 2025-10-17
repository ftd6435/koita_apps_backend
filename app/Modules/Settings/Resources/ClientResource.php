<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transforme la ressource en tableau JSON sans valeurs nulles.
     */
    public function toArray($request): array
    {
        return array_filter([
            'id'              => $this->id,
            'nom_complet'     => $this->nom_complet,
            'raison_sociale'  => $this->raison_sociale,
            'pays'            => $this->pays,
            'ville'           => $this->ville,
            'adresse'         => $this->adresse,
            'telephone'       => $this->telephone,
            'email'           => $this->email,

            // 🔹 Informations d’audit
            'created_by'      => $this->createur?->name,
            'modify_by'       => $this->modificateur?->name,

            // 🔹 Dates formatées
            'created_at'      => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'      => $this->updated_at?->format('Y-m-d H:i:s'),

            // 🔹 Attribut intelligent (nom affichable)
            'nom_affichage'   => $this->nom_affichage,
        ], fn($value) => !is_null($value));
    }
}

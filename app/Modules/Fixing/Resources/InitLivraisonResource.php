<?php

namespace App\Modules\Fixing\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InitLivraisonResource extends JsonResource
{
    /**
     * 🔹 Transforme la ressource en tableau JSON
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id ?? null,
            'reference'   => $this->reference ?? '',
            'commentaire' => $this->commentaire ?? '',
            'statut'      => $this->statut ?? 'encours',

            // 🔹 Client associé (sécurisé avec optional)
            'client' => $this->whenLoaded('client', [
                'id'        => optional($this->client)->id,
                'nom'       => optional($this->client)->nom,
                'prenom'    => optional($this->client)->prenom,
                'telephone' => optional($this->client)->telephone,
                'email'     => optional($this->client)->email,
                'adresse'   => optional($this->client)->adresse,
            ]),

            // 🔹 Audit sécurisé
            'created_by' => optional($this->createur)->name ?? 'Inconnu',
            'modify_by'  => optional($this->modificateur)->name ?? null,

            // 🔹 Dates formatées
            'created_at' => $this->created_at
                ? $this->created_at->format('d-m-Y H:i:s')
                : null,

            'updated_at' => $this->updated_at
                ? $this->updated_at->format('d-m-Y H:i:s')
                : null,
        ];
    }
}

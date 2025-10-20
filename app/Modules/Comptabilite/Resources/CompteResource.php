<?php

namespace App\Modules\Comptabilite\Resources;

use App\Traits\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    use Helper;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'libelle' => $this->libelle,
            'numero_compte' => $this->numero_compte,
            'solde_initial' => $this->solde_initial,
            // 'solde' => $this->soldeCompte($this->id, $this->solde_initial),
            'commentaire' => $this->commentaire,

            // Relationships here...
            'opt_fournisseurs' => FournisseurOperationResource::collection($this->whenLoaded('fournisseurOperations')),

            'devise' => $this->devise ? [
                'id' => $this->devise->id ?? null,
                'libelle' => $this->devise->libelle ?? null,
                'symbole' => $this->devise->symbole ?? null,
                'taux_change' => $this->devise->taux_change ?? null,
            ] : null,

            'createdBy' => $this->createdBy ? [
                'id' => $this->createdBy->id ?? null,
                'name' => $this->createdBy->name ?? null,
                'email' => $this->createdBy->email ?? null,
                'telephone' => $this->createdBy->telephone ?? null,
                'adresse' => $this->createdBy->adresse ?? null,
                'role' => $this->createdBy->role ?? null,
            ] : null,

            'updatedBy' => $this->updatedBy ? [
                'id' => $this->updatedBy->id ?? null,
                'name' => $this->updatedBy->name ?? null,
                'email' => $this->updatedBy->email ?? null,
                'telephone' => $this->updatedBy->telephone ?? null,
                'adresse' => $this->updatedBy->adresse ?? null,
                'role' => $this->updatedBy->role,
            ] : null,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

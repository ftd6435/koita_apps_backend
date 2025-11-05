<?php

namespace App\Modules\Comptabilite\Resources;

use App\Modules\Administration\Resources\FournisseurResource;
use App\Traits\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FournisseurOperationResource extends JsonResource
{
    use Helper;

    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,

            'fournisseur' => [
                'id' => $this->fournisseur->id,
                'name' => $this->fournisseur->name,
                'adresse' => $this->fournisseur->adresse ?? null,
                'telephone' => $this->fournisseur->telephone,
                'solde' => $this->soldeGlobalFournisseur($this->fournisseur->id),
                'image' => is_null($this->fournisseur->image) ? asset('/images/male.jpg') : asset('/storage/images/fournisseurs/'.$this->fournisseur->image)
            ],

            'type_operation' => [
                'id' => $this->typeOperation->id,
                'libelle' => $this->typeOperation->libelle,
                'nature' => $this->typeOperation->nature,
            ],

            'devise' => [
                'id' => $this->devise->id ?? null,
                'libelle' => $this->devise->libelle ?? null,
                'symbole' => $this->devise->symbole ?? null,
                'taux_change' => $this->devise->taux_change ?? null,
            ],

            'compte' => [
                'id' => $this->compte->id,
                'libelle' => $this->compte->libelle,
                'numero_compte' => $this->compte->numero_compte,
                'commentaire' => $this->compte->commentaire ?? null,
            ],

            'date_operation' => $this->date_operation ?? null,
            'reference' => $this->reference ?? null,
            'taux_jour' => $this->taux,
            'montant' => $this->montant,
            'commentaire' => $this->commentaire ?? null,

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

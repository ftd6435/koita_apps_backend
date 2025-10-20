<?php

namespace App\Modules\Purchase\Resources;

use App\Traits\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarreResource extends JsonResource
{
    use Helper;

    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'poid_pure' => $this->poid_pure,
            'carrat_pure' => $this->carrat_pure,
            'pureter' => $this->pureter($this->poid_pure, $this->carrat_pure),
            'densite' => $this->densite,
            'status' => $this->status,
            'is_fixed' => $this->is_fixed,

            'achat' => $this->achat ? [
                'id' => $this->achat->id,
                'reference' => $this->achat->reference,
                'fournisseur' => [
                    'id' => $this->achat->fournisseur->id,
                    'name' => $this->achat->fournisseur->name,
                    'adresse' => $this->achat->fournisseur->adresse ?? null,
                    'telephone' => $this->achat->fournisseur->telephone,
                    'image' => is_null($this->achat->fournisseur->image) ? asset('/images/male.jpg') : asset('/storage/images/fournisseurs/'.$this->achat->fournisseur->image)
                ],
                'lot' => [
                    'id' => $this->achat->lot->id,
                    'libelle' => $this->achat->lot->libelle,
                    'commentaire' => $this->achat->lot->commentaire ?? null,
                    'lot_status' => $this->achat->lot->status ?? null,
                ],
                'commentaire' => $this->achat->commentaire ?? null,
                'etat_achat' => $this->achat->etat,
                'achat_status' => $this->achat->status,
            ] : null,

            'createdBy' => $this->createdBy ? [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
                'email' => $this->createdBy->email ?? null,
                'telephone' => $this->createdBy->telephone ?? null,
                'adresse' => $this->createdBy->adresse ?? null,
                'role' => $this->createdBy->role ?? null,
            ] : null,

            'updatedBy' => $this->updatedBy ? [
                'id' => $this->updatedBy->id,
                'name' => $this->updatedBy->name,
                'email' => $this->updatedBy->email ?? null,
                'telephone' => $this->updatedBy->telephone ?? null,
                'adresse' => $this->updatedBy->adresse ?? null,
                'role' => $this->updatedBy->role ?? null,
            ] : null,

            'created_at' => optional($this->created_at)->format('d-m-Y H:i:s'),
            'updated_at' => optional($this->updated_at)->format('d-m-Y H:i:s'),
        ];
    }
}

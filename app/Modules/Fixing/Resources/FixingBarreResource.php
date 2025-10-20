<?php

namespace App\Modules\Fixing\Resources;

use App\Traits\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FixingBarreResource extends JsonResource
{
    use Helper;

    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,

            'barre' => [
                'id' => $this->barre->id ?? null,
                'poid_pure' => $this->barre->poid_pure ?? null,
                'carrat_pure' => $this->barre->carrat_pure ?? null,
                'densite' => $this->barre->densite ?? null,
                'pureter' => $this->pureter($this->barre->poid_pure, $this->barre->carrat_pure),
                'barre_status' => $this->barre->status ?? null,
                'is_fixed' => $this->barre->is_fixed,
                'apres_fonde' => $this->barre->status == 'fondue' ? $this->barreFondue($this->barre->id) : null,
                'montant_barre' => $this->montantBarre($this->barre->id, $this->fixing->unit_price)
            ],

            'fixing' => [
                'id' => $this->fixing->id,
                'poids_provisoir' => $this->fixing->poids_pro,
                'carrat_provisoir' => $this->fixing->carrat_moyenne,
                'discount' => $this->fixing->discount ?? null,
                'bourse' => $this->fixing->bourse ?? null,
                'unit_price' => $this->fixing->unit_price ?? null,
                'fournisseur' => [
                    'id' => $this->fixing->fournisseur->id,
                    'name' => $this->fixing->fournisseur->name,
                    'adresse' => $this->fixing->fournisseur->adresse ?? null,
                    'telephone' => $this->fixing->fournisseur->telephone,
                    'image' => is_null($this->fixing->fournisseur->image) ? asset('/images/male.jpg') : asset('/storage/images/fournisseurs/'.$this->fixing->fournisseur->image)
                ],
                'status_fixing' => $this->fixing->status,
            ],

            // Created and updated by users
            'createdBy' => $this->createdBy ? [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
                'email' => $this->createdBy->email ?? null,
                'telephone' => $this->createdBy->telephone ?? null,
                'adresse' => $this->createdBy->adresse ?? null,
                'role' => $this->createdBy->role,
            ] : null,

            'updatedBy' => $this->updatedBy ? [
                'id' => $this->updatedBy->id,
                'name' => $this->updatedBy->name,
                'email' => $this->updatedBy->email ?? null,
                'telephone' => $this->updatedBy->telephone ?? null,
                'adresse' => $this->updatedBy->adresse ?? null,
                'role' => $this->updatedBy->role,
            ] : null,

            // 🔹 Dates formatées
            'created_at' => $this->created_at ? $this->created_at->format('d-m-Y H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('d-m-Y H:i:s') : null,
        ];
    }
}

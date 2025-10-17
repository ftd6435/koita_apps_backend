<?php

namespace App\Modules\Fixing\Resources;

use App\Traits\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FixingResource extends JsonResource
{
    use Helper;

    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,

            // Fournisseur relationship
            'fournisseur' => $this->whenLoaded('fournisseur', function () {
                return [
                    'id' => $this->fournisseur->id ?? null,
                    'name' => $this->fournisseur->name ?? null,
                    'adresse' => $this->fournisseur->adresse ?? null,
                    'telephone' => $this->fournisseur->telephone ?? null,
                    'image' => is_null($this->fournisseur->image) ? asset('/images/male.jpg') : asset('/storage/images/fournisseurs/'.$this->fournisseur->image),
                ];
            }),

            // Devise relationship
            'devise' => $this->devise ? $this->whenLoaded('devise', function () {
                return [
                    'id' => $this->devise->id ?? null,
                    'libelle' => $this->devise->libelle ?? null,
                    'symbole' => $this->devise->symbole ?? null,
                    'taux_change' => $this->devise->taux_change ?? null,
                ];
            }) : null,

            // Fixing Barre relationship
            'fixing_barres' => FixingBarreResource::collection($this->whenLoaded('fixingBarres')),

            'poids_provisoir' => $this->poids_pro,
            'carrat_provisoir' => $this->carrat_moyenne,

            'poids_fixing' => number_format($this->poidsFixing($this->id), 3),
            'carrat_fixing' => number_format($this->carratFixing($this->id), 3),
            'montant_total' => number_format($this->montantFixing($this->id), 3),

            'discount' => $this->discount ?? null,
            'bourse' => $this->bourse ?? null,
            'unit_price' => $this->unit_price ?? null,
            'status_fixing' => $this->status,

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

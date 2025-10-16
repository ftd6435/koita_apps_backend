<?php

namespace App\Modules\Purchase\Resources;

use App\Traits\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

class AchatResource extends JsonResource
{
    use Helper;

    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? null,
            'reference' => $this->reference,
            'commentaire' => $this->commentaire,
            'poids_total' => $this->barres->sum('poid_pure'),
            'carrat_moyenne' => $this->carratMoyenne($this->id),
            'achat_status' => $this->status,

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

            // Lot relationship
            'lot' => $this->whenLoaded('lot', function () {
                return [
                    'id' => $this->lot->id ?? null,
                    'libelle' => $this->lot->libelle ?? null,
                    'commentaire' => $this->lot->commentaire ?? null,
                    'date' => $this->lot->date ?? null,
                    'lot_status' => $this->lot->status ?? null,
                ];
            }),

            'barres' => $this->barres->map(function ($barre){
                return [
                    'id' => $barre->id ?? null,
                    'poid_pure' => $barre->poid_pure ?? null,
                    'carrat_pure' => $barre->carrat_pure ?? null,
                    'densite' => $barre->densite ?? null,
                    'barre_status' => $barre->status ?? null,
                ];
            }),

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

            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at->format('d-m-Y H:i:s'),
        ];
    }
}

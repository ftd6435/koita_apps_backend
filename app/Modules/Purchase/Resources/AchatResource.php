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
            'id' => $this->id,
            'reference' => $this->reference,
            'commentaire' => $this->commentaire,
            'poids_total' => $this->barres->sum('poid_pure'),
            'carrat_moyenne' => $this->carratMoyenne($this->id),
            'achat_status' => $this->status,

            // Fournisseur relationship
            'fournisseur' => $this->whenLoaded('fournisseur', function () {
                return [
                    'id' => $this->fournisseur->id,
                    'name' => $this->fournisseur->name,
                    'adresse' => $this->fournisseur->adresse,
                    'telephone' => $this->fournisseur->telephone,
                    'image' => $this->fournisseur->image,
                ];
            }),

            // Lot relationship
            'lot' => $this->whenLoaded('lot', function () {
                return [
                    'id' => $this->lot->id,
                    'libelle' => $this->lot->libelle,
                    'commentaire' => $this->lot->commentaire,
                    'date' => $this->lot->date,
                    'lot_status' => $this->lot->status,
                ];
            }),

            // ✅ Achats relationship
            'barres' => $this->whenLoaded('barres', function () {
                return BarreResource::collection($this->barres);
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

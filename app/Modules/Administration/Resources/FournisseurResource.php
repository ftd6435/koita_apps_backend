<?php

namespace App\Modules\Administration\Resources;

use App\Modules\Purchase\Resources\AchatResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FournisseurResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id ?? null,
            'name' => $this->name,
            'adresse' => $this->adresse,
            'telephone' => $this->telephone ?? null,
            'image' => is_null($this->image)  ? asset('/images/male.jpg') : asset('/storage/images/fournisseurs/'.$this->image),

            // ✅ Achats relationship
            'achats' => $this->achats->map(function ($achat){
                return [
                    'id' => $this->achat->id,
                    'reference' => $this->achat->reference,
                    'lot' => [
                        'id' => $this->achat->lot->id,
                        'libelle' => $this->achat->lot->libelle,
                        'commentaire' => $this->achat->lot->commentaire ?? null,
                        'lot_status' => $this->achat->lot->status ?? null,
                    ],
                    'commentaire' => $this->achat->commentaire ?? null,
                    'achat_status' => $this->achat->status,
                ];
            }),

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

            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at->format('d-m-Y H:i:s'),
        ];
    }
}

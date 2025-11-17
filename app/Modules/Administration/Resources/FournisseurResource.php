<?php

namespace App\Modules\Administration\Resources;

use App\Modules\Comptabilite\Resources\FournisseurOperationResource;
use App\Modules\Purchase\Resources\AchatResource;
use App\Traits\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FournisseurResource extends JsonResource
{
    use Helper;

    public function toArray(Request $request)
    {
        return [
            'id' => $this->id ?? null,
            'name' => $this->name,
            'adresse' => $this->adresse,
            'telephone' => $this->telephone ?? null,
            'solde' => $this->soldeGlobalFournisseur($this->id),
            'poids_non_fixer' => $this->poidsNonFixer($this->id),
            'carratMoy_nonFixer' => $this->carratMoyenNonFixer($this->id),
            'image' => is_null($this->image) ? asset('/images/male.jpg') : asset('/storage/images/fournisseurs/'.$this->image),

            // ✅ Achats relationship
            'achats' => AchatResource::collection($this->whenLoaded('achats')),
            'operations' => FournisseurOperationResource::collection(
                $this->whenLoaded('operations', $this->operations ?? collect([]))
            ),
            'historiques' => $this->historiqueFournisseurComplet($this->id),
            'fixings' => $this->fixings->map(function ($fixing) {
                if ($fixing->status == 'en attente') {
                    return [
                        'fixing_id' => $fixing->id ?? 0,
                        'poids_pro' => $fixing->poids_pro ?? 0,
                        'carrat_pro' => $fixing->carrat_moyenne ?? 0,
                        'discount' => $fixing->discount ?? 0,
                        'bourse' => $fixing->bourse ?? 0,
                        'unit_price' => $fixing->unit_price ?? 0,
                        'devise_symbole' => $fixing->devise->symbole ?? '',
                        'status_fixing' => $fixing->status,
                    ];
                }
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

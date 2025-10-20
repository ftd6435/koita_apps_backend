<?php

namespace App\Modules\Purchase\Resources;

use App\Modules\Purchase\Models\Barre;
use App\Traits\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

class AchatResource extends JsonResource
{
    use Helper;

    public function toArray($request): array
    {
        $barres_count = Barre::where('achat_id', $this->id)->count();
        $fixed_barres = Barre::where('achat_id', $this->id)->where('is_fixed', true)->count();

        $fixed_achat = "";

        if($barres_count > 0){
            if($barres_count == $fixed_barres){
                $fixed_achat = "completer";
            }elseif($fixed_barres > 0 && $fixed_barres < $barres_count){
                $fixed_achat = "partiel";
            }else{
                $fixed_achat = "non fixer";
            }
        }

        return [
            'id' => $this->id ?? null,
            'reference' => $this->reference,
            'commentaire' => $this->commentaire,
            'poids_total' => number_format($this->barres->sum('poid_pure'), 3),
            'carrat_moyenne' => $this->carratMoyenne($this->id),
            'etat_achat' => $this->etat,
            'achat_status' => $this->status,
            'fixed_achat' => $fixed_achat,

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

            'barres' => $this->barres->map(function ($barre) {
                return [
                    'id' => $barre->id ?? null,
                    'poid_pure' => $barre->poid_pure ?? null,
                    'carrat_pure' => $barre->carrat_pure ?? null,
                    'densite' => $barre->densite ?? null,
                    'pureter' => $this->pureter($barre->poid_pure, $barre->carrat_pure),
                    'barre_status' => $barre->status ?? null,
                    'is_fixed' => $barre->is_fixed,
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

<?php

namespace App\Modules\Fondation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Purchase\Models\Barre;
use App\Modules\Purchase\Resources\BarreResource;

class FondationResource extends JsonResource
{
    /**
     * Transforme la ressource en tableau JSON.
     */
    public function toArray(Request $request): array
    {
        // 🔹 Récupérer les barres liées à cette fondation via les IDs
        $barres = Barre::whereIn('id', $this->ids_barres)->get();

        return [
            'id'           => $this->id,
            'ids_barres'   => $this->ids_barres, // renvoyé comme tableau (grâce à ton accessor)
            'poid_fondu'   => (float) $this->poid_fondu,
            'carat_moyen'  => (float) $this->carat_moyen,
            'poids_dubai'  => (float) $this->poids_dubai,
            'carrat_dubai' => (float) $this->carrat_dubai,
            'is_fixed'     => (bool) $this->is_fixed,

            // 🔹 Liste des barres fondues associées
            'barres'       => BarreResource::collection($barres),

            // 🔹 Audit
            'created_by'   => $this->createur?->name,
            'modify_by'    => $this->modificateur?->name,
            'resume'       => $this->resume,
            // 🔹 Dates
            'created_at'   => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'   => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

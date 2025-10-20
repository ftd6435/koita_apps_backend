<?php

namespace App\Modules\Fondation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\Helper;

class FondationResource1 extends JsonResource
{

    use Helper;
    /**
     * Transforme la ressource en tableau JSON.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
           
            'poids_fondu'   => (float) $this->poids_fondu,
            'carrat_fondu'  => (float) $this->carrat_fondu,
            'poids_dubai'   => (float) $this->poids_dubai,
            'carrat_dubai'  => (float) $this->carrat_dubai,
            'purete_locale'   => $this->pureter($this->poids_fondu, $this->carrat_fondu),
            'purete_dubai'    => $this->pureter($this->poids_dubai, $this->carrat_dubai),
            'is_fixed'      => (bool) $this->is_fixed,
            'statut'        => $this->statut,

            // 🔹 Infos d’audit (optionnel)
            'created_by'    => $this->createur?->name,
            'modify_by'     => $this->modificateur?->name,

            // 🔹 Dates (optionnel)
            'created_at'    => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'    => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

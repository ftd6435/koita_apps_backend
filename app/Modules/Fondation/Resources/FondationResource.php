<?php
namespace App\Modules\Fondation\Resources;

use App\Modules\Purchase\Models\Barre;
use App\Modules\Purchase\Resources\BarreResource;
use App\Traits\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FondationResource extends JsonResource
{
    use Helper;

    public function toArray(Request $request): array
    {
        // 🔹 Récupération des barres liées via les IDs
        $barres = Barre::whereIn('id', $this->ids_barres)->get();

        return [
            'id'              => $this->id,
            'ids_barres'      => $this->ids_barres,
            'poids_fondu'     => (float) $this->poids_fondu,
            'carrat_fondu'    => (float) $this->carrat_fondu,
            'poids_dubai'     => (float) $this->poids_dubai,
            'carrat_dubai'    => (float) $this->carrat_dubai,

            // 🔹 Pureté locale & dubai
            'purete_locale'   => $this->pureter($this->poids_fondu, $this->carrat_fondu),
            'purete_dubai'    => $this->pureter($this->poids_dubai, $this->carrat_dubai),

            // 🔹 Statuts & métadonnées
            'is_fixed'        => (bool) $this->is_fixed,
            'statut'          => $this->statut,

            // 🔹 Barres associées
            'barres'          => BarreResource::collection($barres),

            // 🔹 Audit
            'created_by'      => $this->createur?->name,
            'modify_by'       => $this->modificateur?->name,

            // 🔹 Résumé & dates
            'resume'          => $this->resume,
            'created_at'      => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'      => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

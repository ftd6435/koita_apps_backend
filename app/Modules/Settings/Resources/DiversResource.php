<?php
namespace App\Modules\Settings\Resources;

use App\Modules\Settings\Services\DiversService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiversResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $diversService = app(DiversService::class);
        $solde         = $diversService->calculerSoldeDivers($this->id);
        $releve        = $diversService->getReleveDivers($this->id);

        return array_filter([
            'id'             => $this->id,
            'name'           => $this->name,
            'raison_sociale' => $this->raison_sociale,
            'telephone'      => $this->telephone,
            'adresse'        => $this->adresse,
            'type'           => $this->type,
            'soldes'         => $solde,

            'releve'         => $releve,

            'created_by'     => $this->createur?->name,
            'updated_by'     => $this->modificateur?->name,
            'created_at'     => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'     => $this->updated_at?->format('Y-m-d H:i:s'),
        ], fn($value) => ! is_null($value));
    }
}

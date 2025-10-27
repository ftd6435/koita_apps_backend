<?php

namespace App\Modules\Comptabilite\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Settings\Resources\DeviseResource;
use App\Modules\Comptabilite\Resources\TypeOperationResource;
use App\Modules\Comptabilite\Services\CaisseService;
use Carbon\Carbon;

class CaisseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // ✅ Formatage robuste de la date
        $dateOperation = $this->date_operation
            ? Carbon::parse($this->date_operation)->format('Y-m-d')
            : $this->created_at?->format('Y-m-d');

            $soldeGlobal = app(CaisseService::class)->calculerSoldeGlobal();

        return array_filter([
            'id'               => $this->id,
            'reference'        => $this->reference,
            'date_operation'   => $dateOperation,
            'montant'          => (float) $this->montant,
            'commentaire'      => $this->commentaire,
            'taux_jour'      => $this->taux_jour,

            // 🔹 Relations principales
            'type_operation'   => new TypeOperationResource($this->whenLoaded('typeOperation')),
            'devise'           => new DeviseResource($this->whenLoaded('devise')),
            'soldeGlobal'=> $soldeGlobal,
            // 🔹 Audit
            'created_by'       => $this->createur?->name,
            'updated_by'       => $this->modificateur?->name,

            // 🔹 Dates système
            'created_at'       => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'       => $this->updated_at?->format('Y-m-d H:i:s'),
        ], fn($value) => !is_null($value));
    }
}

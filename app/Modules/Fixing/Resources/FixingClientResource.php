<?php

namespace App\Modules\Fixing\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Settings\Resources\ClientResource;
use App\Modules\Settings\Resources\DeviseResource;
use App\Modules\Fixing\Services\FixingClientService;

class FixingClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // 🔹 Calcul complet via le service
        $calculs = app(FixingClientService::class)->calculerFacture($this->id);

        return [
            'id'             => $this->id,
            'poids_pro'      => (float) $this->poids_pro,
           
            'discompte'      => (float) $this->discompte,
            'bourse'         => (float) $this->bourse,
            'status'         => $this->status ?? 'en attente',

            // 🔹 Type du client (local / extra)
            'type_client'    => $calculs['type_client'] ?? null,

            // 🔹 Relations principales
            'client'         => new ClientResource($this->whenLoaded('client')),
            'devise'         => new DeviseResource($this->whenLoaded('devise')),

            
            'calculs' => [
                'prix_unitaire'  => $calculs['prix_unitaire'] ?? null,
                'poids_total'    => $calculs['poids_total'] ?? 0,
              
                'purete_totale'  => $calculs['purete_totale'] ?? 0,
                'total_facture'  => $calculs['total_facture'] ?? 0,
            ],

            // 🔹 Audit
            'created_by'       => $this->createur?->name,
            'modify_by'    => $this->modificateur?->name,

            // 🔹 Dates formatées
            'created_at'     => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'     => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

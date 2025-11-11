<?php

namespace App\Modules\Settings\Resources;

use App\Modules\Fixing\Resources\FixingClientResource;
use App\Modules\Fixing\Resources\InitLivraisonResource;
use App\Modules\Settings\Services\ClientService;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray($request): array
    {
        $clientService = app(ClientService::class);

        // 💰 Soldes du client
        $solde = $clientService->calculerSoldeClient($this->id);

        // 📊 Relevé complet (opérations financières + or)
        $releve = $clientService->getReleveClient($this->id);

        // 📦 Stock du client (livraisons et fixings)
        $stock = $clientService->calculerStockClient($this->id);

        return array_filter([
            'id'             => $this->id,
            'nom_complet'    => $this->nom_complet,
            'raison_sociale' => $this->raison_sociale,
            'type_client'    => $this->type_client,
            'pays'           => $this->pays,
            'ville'          => $this->ville,
            'adresse'        => $this->adresse,
            'telephone'      => $this->telephone,
            'email'          => $this->email,

            // 💰 Soldes actuels
            'soldes'         => $solde['soldes'] ?? [],

            // 🟡 Relevé du client (séparé en deux parties)
            'operations_financieres' => $releve['operations_financieres'] ?? [],
            'operations_or'          => $releve['operations_or'] ?? [],

            // 📦 Informations sur le stock
            'total_livre'    => $stock['total_livre'] ?? 0,
            'total_fixing'   => $stock['total_fixing'] ?? 0,
            'reste_stock'    => $stock['reste_stock'] ?? 0,

            // 🔹 Audit
            'created_by'     => $this->createur?->name,
            'modify_by'      => $this->modificateur?->name,
            'created_at'     => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'     => $this->updated_at?->format('Y-m-d H:i:s'),

            // 🔹 Nom affichable
            'nom_affichage'  => $this->nom_affichage,

            // 🔹 Fixings
            'fixings'        => FixingClientResource::collection(
                $this->whenLoaded('fixings')
            ),

            // 🔹 Livraisons
            'init_livraisons' => InitLivraisonResource::collection(
                $this->whenLoaded('initLivraisons')
            ),
        ], fn($value) => ! is_null($value));
    }
}

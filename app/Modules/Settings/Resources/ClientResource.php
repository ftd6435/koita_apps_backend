<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Fixing\Resources\FixingClientResource;
use App\Modules\Fixing\Resources\InitLivraisonResource;
use App\Modules\Settings\Services\ClientService;

class ClientResource extends JsonResource
{
    /**
     * Transforme la ressource en tableau JSON sans valeurs nulles.
     */
    public function toArray($request): array
    {
        // 🔹 Calcul du solde actuel du client par devise
        $solde = app(ClientService::class)->calculerSoldeClient($this->id);

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

            // 💰 Soldes par devise
            'solde_usd'      => $solde['solde_usd'] ?? 0,
            'solde_gnf'      => $solde['solde_gnf'] ?? 0,

            // 🔹 Informations d’audit
            'created_by'     => $this->createur?->name,
            'modify_by'      => $this->modificateur?->name,

            // 🔹 Dates formatées
            'created_at'     => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'     => $this->updated_at?->format('Y-m-d H:i:s'),

            // 🔹 Nom affichable
            'nom_affichage'  => $this->nom_affichage,

            // 🔹 Fixings du client
            'fixings' => FixingClientResource::collection(
                $this->whenLoaded('fixings')
            ),

            // 🔹 Livraisons initiales liées
            'init_livraisons' => InitLivraisonResource::collection(
                $this->whenLoaded('initLivraisons')
            ),
        ], fn($value) => !is_null($value));
    }
}

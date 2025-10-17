<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Client;
use App\Modules\Settings\Resources\ClientResource;
use Illuminate\Support\Facades\Auth;
use App\Modules\Fixing\Models\InitLivraison;
use App\Modules\Settings\Resources\LivraisonNonFixeeResource;
use Exception;

class ClientService
{
    /**
     * 🔹 Créer un nouveau client
     */
    public function store(array $data)
    {
        try {
            $data['created_by'] = Auth::id();
            $client = Client::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Client créé avec succès.',
                'data'    => new ClientResource(
                    $client->load(['createur', 'modificateur', 'initLivraisons'])
                ),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création du client.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Mettre à jour un client
     */
    public function update(int $id, array $data)
    {
        try {
            $client = Client::findOrFail($id);
            $data['modify_by'] = Auth::id();
            $client->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Client mis à jour avec succès.',
                'data'    => new ClientResource(
                    $client->load(['createur', 'modificateur', 'initLivraisons'])
                ),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour du client.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Supprimer un client
     */
    public function delete(int $id)
    {
        try {
            $client = Client::findOrFail($id);
            $client->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Client supprimé avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression du client.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Récupérer tous les clients avec leurs livraisons
     */
    public function getAll()
    {
        try {
            $clients = Client::with(['createur', 'modificateur', 'initLivraisons'])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'message' => 'Liste des clients récupérée avec succès.',
                'data'   => ClientResource::collection($clients),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des clients.',
                'error'   => $e->getMessage(),
            ]);
        }
    }



    /**
     * 🔹 Récupérer un client spécifique avec ses livraisons
     */
    public function getOne(int $id)
    {
        try {
            $client = Client::with(['createur', 'modificateur', 'initLivraisons'])
                ->findOrFail($id);

            return response()->json([
                'status' => 200,
                'message' => 'Client trouvé avec succès.',
                'data'   => new ClientResource($client),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'Client non trouvé.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

 

public function getLivraisonsNonFixees(int $clientId)
{
    try {
        $livraisons = InitLivraison::with(['fondations'])
            ->where('id_client', $clientId)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status'  => 200,
            'message' => 'Livraisons non fixées récupérées avec succès.',
            'data'    => LivraisonNonFixeeResource::collection($livraisons),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status'  => 500,
            'message' => 'Erreur lors de la récupération des livraisons non fixées.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

}

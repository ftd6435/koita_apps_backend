<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Client;
use App\Modules\Settings\Http\Resources\ClientResource;
use Illuminate\Support\Facades\Auth;
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
                'data'    => new ClientResource($client),
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
                'data'    => new ClientResource($client),
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
     * 🔹 Récupérer tous les clients
     */
    public function getAll()
    {
        try {
            $clients = Client::with(['createur', 'modificateur'])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
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
     * 🔹 Récupérer un client spécifique
     */
    public function getOne(int $id)
    {
        try {
            $client = Client::with(['createur', 'modificateur'])
                ->findOrFail($id);

            return response()->json([
                'status' => 200,
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
}

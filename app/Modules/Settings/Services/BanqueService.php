<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Banque;
use App\Modules\Settings\Http\Resources\BanqueResource;
use Illuminate\Support\Facades\Auth;
use Exception;

class BanqueService
{
    /**
     * 🔹 Créer une banque
     */
    public function store(array $data)
    {
        try {
            $data['created_by'] = Auth::id();
            $banque = Banque::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Banque créée avec succès.',
                'data'    => new BanqueResource($banque),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de la banque.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Mettre à jour une banque
     */
    public function update(int $id, array $data)
    {
        try {
            $banque = Banque::findOrFail($id);
            $data['modify_by'] = Auth::id();
            $banque->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Banque mise à jour avec succès.',
                'data'    => new BanqueResource($banque),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour de la banque.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Supprimer (soft delete) une banque
     */
    public function delete(int $id)
    {
        try {
            $banque = Banque::findOrFail($id);
            $banque->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Banque supprimée avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la banque.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Récupérer toutes les banques
     */
    public function getAll()
    {
        try {
            $banques = Banque::with(['createur', 'modificateur'])
                ->orderByDesc('id')
                ->get();

            return response()->json([
                'status'  => 200,
                'message' => 'Liste des banques récupérée avec succès.',
                'data'    => BanqueResource::collection($banques),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des banques.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Récupérer une banque spécifique
     */
    public function getOne(int $id)
    {
        try {
            $banque = Banque::with(['createur', 'modificateur'])->findOrFail($id);

            return response()->json([
                'status'  => 200,
                'message' => 'Banque récupérée avec succès.',
                'data'    => new BanqueResource($banque),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'Banque introuvable.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }
}

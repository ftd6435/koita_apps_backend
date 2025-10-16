<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Monetaire;
use App\Modules\Settings\Http\Resources\MonetaireResource;
use Illuminate\Support\Facades\Auth;
use Exception;

class MonetaireService
{
    /**
     * 🔹 Créer un monétaire
     */
    public function store(array $data)
    {
        try {
            $data['created_by'] = Auth::id();
            $monetaire = Monetaire::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Monétaire créé avec succès.',
                'data'    => new MonetaireResource($monetaire),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création du monétaire.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Mettre à jour un monétaire
     */
    public function update(int $id, array $data)
    {
        try {
            $monetaire = Monetaire::findOrFail($id);
            $data['modify_by'] = Auth::id();
            $monetaire->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Monétaire mis à jour avec succès.',
                'data'    => new MonetaireResource($monetaire),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour du monétaire.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Supprimer (soft delete) un monétaire
     */
    public function delete(int $id)
    {
        try {
            $monetaire = Monetaire::findOrFail($id);
            $monetaire->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Monétaire supprimé avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression du monétaire.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Récupérer la liste de tous les monétaires
     */
    public function getAll()
    {
        try {
            $monetaires = Monetaire::with(['createur', 'modificateur'])
                ->orderByDesc('id')
                ->get();

            return response()->json([
                'status'  => 200,
                'message' => 'Liste des monétaires récupérée avec succès.',
                'data'    => MonetaireResource::collection($monetaires),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des monétaires.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Récupérer un monétaire spécifique
     */
    public function getOne(int $id)
    {
        try {
            $monetaire = Monetaire::with(['createur', 'modificateur'])->findOrFail($id);

            return response()->json([
                'status'  => 200,
                'message' => 'Monétaire récupéré avec succès.',
                'data'    => new MonetaireResource($monetaire),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'Monétaire introuvable.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }
}

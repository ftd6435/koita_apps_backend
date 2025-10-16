<?php
namespace App\Modules\Settings\Services;

use App\Modules\Settings\Http\Resources\PartenaireResource;
use App\Modules\Settings\Models\Partenaire;
use Exception;
use Illuminate\Support\Facades\Auth;

class PartenaireService
{
    /**
     * 🔹 Créer un partenaire
     */
    public function store(array $data)
    {
        try {
            $data['created_by'] = Auth::id();
            $partenaire         = Partenaire::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Partenaire créé avec succès.',
                'data'    => new PartenaireResource($partenaire),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création du partenaire.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Mettre à jour un partenaire
     */
    public function update(int $id, array $data)
    {
        try {
            $partenaire        = Partenaire::findOrFail($id);
            $data['modify_by'] = Auth::id();
            $partenaire->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Partenaire mis à jour avec succès.',
                'data'    => new PartenaireResource($partenaire),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour du partenaire.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Supprimer (soft delete) un partenaire
     */
    public function delete(int $id)
    {
        try {
            $partenaire = Partenaire::findOrFail($id);
            $partenaire->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Partenaire supprimé avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression du partenaire.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Récupérer tous les partenaires
     */
    public function getAll()
    {
        try {
            $partenaires = Partenaire::with(['createur', 'modificateur'])
                ->orderByDesc('id')
                ->get();

            return response()->json([
                'status'  => 200,
                'message' => 'Liste des partenaires récupérée avec succès.',
                'data'    => PartenaireResource::collection($partenaires),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des partenaires.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Récupérer un partenaire spécifique
     */
    public function getOne(int $id)
    {
        try {
            $partenaire = Partenaire::with(['createur', 'modificateur'])->findOrFail($id);

            return response()->json([
                'status'  => 200,
                'message' => 'Partenaire récupéré avec succès.',
                'data'    => new PartenaireResource($partenaire),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'Partenaire introuvable.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }
}

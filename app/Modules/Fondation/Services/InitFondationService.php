<?php

namespace App\Modules\Fondation\Services;

use App\Modules\Fondation\Models\InitFondation;
use App\Modules\Fondation\Resources\InitFondationResource;
use Exception;

class InitFondationService
{
    /**
     * 🔹 Lister toutes les initialisations de fondations avec leurs relations
     */
    public function getAll()
    {
        try {
            $initFondations = InitFondation::with([
                'fondations',
                'createur',
                'modificateur'
            ])
            ->orderByDesc('id')
            ->get();

            return response()->json([
                'status'  => 200,
                'message' => 'Liste des initialisations de fondation récupérée avec succès.',
                'data'    => InitFondationResource::collection($initFondations),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des initialisations de fondation.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Récupérer une initialisation spécifique avec ses relations
     */
    public function getOne(int $id)
    {
        try {
            $initFondation = InitFondation::with([
                'fondations',
                'createur',
                'modificateur'
            ])->find($id);

            if (! $initFondation) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Initialisation de fondation non trouvée.',
                ], 404);
            }

            return response()->json([
                'status'  => 200,
                'message' => 'Initialisation de fondation récupérée avec succès.',
                'data'    => new InitFondationResource($initFondation),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération de l\'initialisation.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Supprimer une initialisation (et ses fondations associées grâce au cascadeOnDelete)
     */
    public function delete(int $id)
    {
        try {
            $initFondation = InitFondation::find($id);

            if (! $initFondation) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Initialisation de fondation non trouvée.',
                ], 404);
            }

            $initFondation->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Initialisation de fondation supprimée avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de l\'initialisation.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}

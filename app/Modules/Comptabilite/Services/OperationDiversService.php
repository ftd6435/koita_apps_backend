<?php
namespace App\Modules\Comptabilite\Services;

use App\Modules\Comptabilite\Models\OperationDivers;
use App\Modules\Comptabilite\Models\TypeOperation;
use App\Modules\Comptabilite\Resources\OperationDiversResource;
use Exception;
use Illuminate\Support\Facades\Auth;

class OperationDiversService
{
    /**
     * 🔹 Enregistrer une nouvelle opération divers
     */
    public function store(array $data)
    {
        try {
            $data['created_by'] = Auth::id();

            // ✅ Vérifie si l’opération est une sortie (nature = 0)
            $typeOperation = TypeOperation::find($data['id_type_operation']);

            if ($typeOperation && $typeOperation->nature == 0) {
                // ✅ Vérification du solde du compte avant enregistrement
                $verification = CompteService::verifierSoldeAvantOperation(
                    $data['id_compte'],
                    $data['id_devise'],
                    $data['montant']
                );

                if ($verification['status'] === false) {
                    return response()->json([
                        'status'  => 422,
                        'message' => $verification['message'],
                        'data'    => [
                            'solde_disponible' => $verification['solde'],
                            'montant_demande'  => $data['montant'],
                        ],
                    ], 422);
                }
            }

            // ✅ Enregistrement de l’opération après validation
            $operation = OperationDivers::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Opération divers enregistrée avec succès.',
                'data'    => new OperationDiversResource(
                    $operation->load(['divers', 'typeOperation', 'devise', 'createur'])
                ),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l’enregistrement de l’opération divers.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Mettre à jour une opération divers
     */

    public function update(int $id, array $data)
    {
        try {
            $operation          = OperationDivers::findOrFail($id);
            $data['updated_by'] = Auth::id();
            $operation->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Opération divers mise à jour avec succès.',
                'data'    => new OperationDiversResource(
                    $operation->load(['divers', 'typeOperation', 'devise', 'modificateur'])
                ),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour de l’opération divers.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Supprimer une opération divers
     */
    
    public function delete(int $id)
    {
        try {
            $operation = OperationDivers::findOrFail($id);
            $operation->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Opération divers supprimée avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de l’opération divers.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Récupérer toutes les opérations divers
     */
    public function getAll()
    {
        try {
            $operations = OperationDivers::with(['divers', 'typeOperation', 'devise', 'createur'])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status'  => 200,
                'message' => 'Liste des opérations divers récupérée avec succès.',
                'data'    => OperationDiversResource::collection($operations),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des opérations divers.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Récupérer une opération divers spécifique
     */
    public function getOne(int $id)
    {
        try {
            $operation = OperationDivers::with(['divers', 'typeOperation', 'devise', 'createur'])
                ->findOrFail($id);

            return response()->json([
                'status'  => 200,
                'message' => 'Détails de l’opération divers récupérés avec succès.',
                'data'    => new OperationDiversResource($operation),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'Opération divers introuvable.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
}

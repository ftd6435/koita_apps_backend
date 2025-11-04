<?php
namespace App\Modules\Comptabilite\Services;

use App\Modules\Comptabilite\Models\OperationClient;
use App\Modules\Comptabilite\Models\TypeOperation;
use App\Modules\Comptabilite\Resources\OperationClientResource;
use Exception;
use Illuminate\Support\Facades\Auth;

class OperationClientService
{
    /**
     * 🔹 Enregistrer une nouvelle opération client
     */
    public function store(array $data)
    {
        try {
            $data['created_by'] = Auth::id();

            // ✅ Vérifie la présence d’un compte et d’une devise

            // Récupère le type d’opération pour savoir si c’est une sortie (nature = 0)
            $typeOperation = TypeOperation::find($data['id_type_operation']);

            if ($typeOperation && $typeOperation->nature == 0) {
                // ✅ Vérifie le solde avant d’autoriser l’opération
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

            // ✅ Si tout est bon → on enregistre l’opération
            $operation = OperationClient::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Opération client enregistrée avec succès.',
                'data'    => new OperationClientResource(
                    $operation->load(['client', 'typeOperation', 'devise', 'createur'])
                ),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l’enregistrement de l’opération client.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Mettre à jour une opération client
     */
    public function update(int $id, array $data)
    {
        try {
            $operation          = OperationClient::findOrFail($id);
            $data['updated_by'] = Auth::id();
            $operation->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Opération client mise à jour avec succès.',
                'data'    => new OperationClientResource(
                    $operation->load(['client', 'typeOperation', 'devise', 'modificateur'])
                ),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour de l’opération client.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Supprimer une opération client
     */
    public function delete(int $id)
    {
        try {
            $operation = OperationClient::findOrFail($id);
            $operation->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Opération client supprimée avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de l’opération client.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Récupérer toutes les opérations clients
     */
    public function getAll()
    {
        try {
            $operations = OperationClient::with(['client', 'typeOperation', 'devise', 'createur'])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status'  => 200,
                'message' => 'Liste des opérations clients récupérée avec succès.',
                'data'    => OperationClientResource::collection($operations),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des opérations clients.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * 🔹 Récupérer une opération client spécifique
     */
    public function getOne(int $id)
    {
        try {
            $operation = OperationClient::with(['client', 'typeOperation', 'devise', 'createur'])
                ->findOrFail($id);

            return response()->json([
                'status'  => 200,
                'message' => 'Détails de l’opération client récupérés avec succès.',
                'data'    => new OperationClientResource($operation),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'Opération client introuvable.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
}

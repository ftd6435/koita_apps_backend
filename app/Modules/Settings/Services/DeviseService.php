<?php
namespace App\Modules\Settings\Services;

use App\Modules\Settings\Resources\DeviseResource;
use App\Modules\Settings\Models\Devise;
use Exception;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeviseService
{
    /**
     * 🔹 Créer une devise
     */
    public function store(array $data)
    {
        try {
            $data['created_by'] = Auth::id();
            $devise             = Devise::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Devise créée avec succès.',
                'data'    => new DeviseResource($devise),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de la devise.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Mettre à jour une devise
     */
    public function update(int $id, array $data)
    {
        try {
            $devise            = Devise::findOrFail($id);
            $data['modify_by'] = Auth::id();
            $devise->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Devise mise à jour avec succès.',
                'data'    => new DeviseResource($devise),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour de la devise.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Supprimer (soft delete) une devise
     */
    public function delete(int $id)
    {
        try {
            $devise = Devise::findOrFail($id);
            $devise->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Devise supprimée avec succès.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la devise.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * 🔹 Récupérer toutes les devises
     */
    public function getAll()
    {
        try {
            $devises = Devise::with(['createur', 'modificateur'])
                ->orderByDesc('id')
                ->get();

            return response()->json([
                'status'  => 200,
                'message' => 'Liste des devises récupérée avec succès.',
                'data'    => DeviseResource::collection($devises),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des devises.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Récupérer une devise spécifique
     */
    public function getOne(int $id)
    {
        try {
            $devise = Devise::with(['createur', 'modificateur'])->findOrFail($id);

            return response()->json([
                'status'  => 200,
                'message' => 'Devise récupérée avec succès.',
                'data'    => new DeviseResource($devise),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'Devise introuvable.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }

    public static function getTauxJour(string $from, string $to): ?float
    {
        try {
            $response = Http::get("https://api.exchangerate-api.com/v4/latest/{$from}");

            if ($response->failed()) {
                Log::error("Erreur API taux de change : {$response->status()}");
                return null;
            }

            $data = $response->json();

            if (! isset($data['rates'][$to])) {
                Log::warning("Taux introuvable pour la devise cible : {$to}");
                return null;
            }

            return (float) $data['rates'][$to];
        } catch (\Exception $e) {
            Log::error("Erreur récupération taux de change : " . $e->getMessage());
            return null;
        }
    }
    
}

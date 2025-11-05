<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Requests\StoreDeviseRequest;
use App\Modules\Settings\Requests\UpdateDeviseRequest;
use App\Modules\Settings\Services\DeviseService;

class DeviseController extends Controller
{
    protected DeviseService $deviseService;

    public function __construct(DeviseService $deviseService)
    {
        $this->deviseService = $deviseService;
    }

    /**
     * 🔹 Liste des devises
     */
    public function index()
    {
        return $this->deviseService->getAll();
    }

    /**
     * 🔹 Création d'une devise
     */
    public function store(StoreDeviseRequest $request)
    {
        return $this->deviseService->store($request->validated());
    }

    /**
     * 🔹 Afficher une devise spécifique
     */
    public function show(int $id)
    {
        return $this->deviseService->getOne($id);
    }

    /**
     * 🔹 Mettre à jour une devise
     */
    public function update(UpdateDeviseRequest $request, int $id)
    {
        return $this->deviseService->update($id, $request->validated());
    }

    /**
     * 🔹 Supprimer une devise (soft delete)
     */
    public function destroy(int $id)
    {
        return $this->deviseService->delete($id);
    }

    public function testTaux()
    {
        $from = 'GNF';
        $to   = 'USD';

       $taux = DeviseService::getTauxJour($from, $to);

        if ($taux === null) {
            return response()->json([
                'status'  => 404,
                'message' => "Impossible d’obtenir le taux entre {$from} et {$to}",
            ], 404);
        }

        return response()->json([
            'status'  => 200,
            'message' => "Taux récupéré avec succès",
            'data'    => [
                'from' => $from,
                'to'   => $to,
                'taux' => $taux,
            ],
        ]);
    }
}

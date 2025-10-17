<?php

namespace App\Modules\Fixing\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fixing\Requests\StoreExpeditionRequest;
use App\Modules\Fixing\Services\ExpeditionService;

class ExpeditionController extends Controller
{
    protected ExpeditionService $expeditionService;

    /**
     * Injection du service dans le contrôleur
     */
    public function __construct(ExpeditionService $expeditionService)
    {
        $this->expeditionService = $expeditionService;
    }

    /**
     * 🔹 Créer une nouvelle expédition
     */
    public function store(StoreExpeditionRequest $request)
    {
        return $this->expeditionService->store($request->validated());
    }

    /**
     * 🔹 Lister toutes les expéditions
     */
    public function index()
    {
        return $this->expeditionService->getAll();
    }

    /**
     * 🔹 Récupérer une expédition spécifique
     */
    public function show(int $id)
    {
        return $this->expeditionService->getOne($id);
    }

    /**
     * 🔹 Supprimer une expédition
     */
    public function destroy(int $id)
    {
        return $this->expeditionService->delete($id);
    }
}

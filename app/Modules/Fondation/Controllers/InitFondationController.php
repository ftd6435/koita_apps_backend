<?php

namespace App\Modules\Fondation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fondation\Services\InitFondationService;
use Illuminate\Http\JsonResponse;

class InitFondationController extends Controller
{
    protected InitFondationService $initFondationService;

    public function __construct(InitFondationService $initFondationService)
    {
        $this->initFondationService = $initFondationService;
    }

    /**
     * 🔹 Récupérer la liste de toutes les initialisations de fondation
     */
    public function index(): JsonResponse
    {
        return $this->initFondationService->getAll();
    }

    /**
     * 🔹 Récupérer une initialisation spécifique par son ID
     */
    public function show(int $id): JsonResponse
    {
        return $this->initFondationService->getOne($id);
    }

    /**
     * 🔹 Supprimer une initialisation de fondation
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->initFondationService->delete($id);
    }
}

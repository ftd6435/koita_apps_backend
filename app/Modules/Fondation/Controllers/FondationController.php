<?php
namespace App\Modules\Fondation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fondation\Requests\StoreFondationRequest;
use App\Modules\Fondation\Services\FondationService;

class FondationController extends Controller
{
    protected FondationService $fondationService;

    /**
     * Injection du service via le constructeur.
     */
    public function __construct(FondationService $fondationService)
    {
        $this->fondationService = $fondationService;
    }

    /**
     * 🔹 Créer une nouvelle fondation.
     */
    public function store(StoreFondationRequest $request)
    {
        return $this->fondationService->store($request->validated());
    }

    /**
     * 🔹 Lister toutes les fondations.
     */
    public function index()
    {
        return $this->fondationService->getAll();
    }

    public function listeFondationNonFondue()
    {
        return $this->fondationService->getAll1();
    }

    /**
     * 🔹 Afficher les détails d’une fondation.
     */
    public function show(int $id)
    {
        return $this->fondationService->getOne($id);
    }

    /**
     * 🔹 Supprimer une fondation (soft delete).
     */
    public function destroy(int $id)
    {
        return $this->fondationService->delete($id);
    }
}

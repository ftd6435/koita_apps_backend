<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\StoreDeviseRequest;
use App\Modules\Settings\Http\Requests\UpdateDeviseRequest;
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
}

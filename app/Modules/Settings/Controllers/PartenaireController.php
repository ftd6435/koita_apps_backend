<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\StorePartenaireRequest;
use App\Modules\Settings\Http\Requests\UpdatePartenaireRequest;
use App\Modules\Settings\Services\PartenaireService;

class PartenaireController extends Controller
{
    protected PartenaireService $partenaireService;

    public function __construct(PartenaireService $partenaireService)
    {
        $this->partenaireService = $partenaireService;
    }

    /**
     * 🔹 Récupérer tous les partenaires
     */
    public function index()
    {
        return $this->partenaireService->getAll();
    }

    /**
     * 🔹 Créer un partenaire
     */
    public function store(StorePartenaireRequest $request)
    {
        return $this->partenaireService->store($request->validated());
    }

    /**
     * 🔹 Afficher un partenaire spécifique
     */
    public function show(int $id)
    {
        return $this->partenaireService->getOne($id);
    }

    /**
     * 🔹 Mettre à jour un partenaire
     */
    public function update(UpdatePartenaireRequest $request, int $id)
    {
        return $this->partenaireService->update($id, $request->validated());
    }

    /**
     * 🔹 Supprimer (soft delete) un partenaire
     */
    public function destroy(int $id)
    {
        return $this->partenaireService->delete($id);
    }
}

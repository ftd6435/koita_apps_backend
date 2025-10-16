<?php

namespace App\Modules\Fixing\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fixing\Requests\StoreInitLivraisonRequest;
use App\Modules\Fixing\Services\InitLivraisonService;

class InitLivraisonController extends Controller
{
    protected InitLivraisonService $initLivraisonService;

    /**
     * Injection du service dans le constructeur
     */
    public function __construct(InitLivraisonService $initLivraisonService)
    {
        $this->initLivraisonService = $initLivraisonService;
    }

    /**
     * 🔹 Liste de toutes les livraisons
     */
    public function index()
    {
        return $this->initLivraisonService->getAll();
    }

    /**
     * 🔹 Créer une nouvelle livraison
     */
    public function store(StoreInitLivraisonRequest $request)
    {
        return $this->initLivraisonService->store($request->validated());
    }

    /**
     * 🔹 Récupérer une livraison par son ID
     */
    public function show(int $id)
    {
        return $this->initLivraisonService->getOne($id);
    }

    /**
     * 🔹 Supprimer une livraison (soft delete)
     */
    public function destroy(int $id)
    {
        return $this->initLivraisonService->delete($id);
    }
}

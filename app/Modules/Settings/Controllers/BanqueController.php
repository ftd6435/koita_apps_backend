<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\StoreBanqueRequest;
use App\Modules\Settings\Http\Requests\UpdateBanqueRequest;
use App\Modules\Settings\Services\BanqueService;

class BanqueController extends Controller
{
    protected BanqueService $banqueService;

    public function __construct(BanqueService $banqueService)
    {
        $this->banqueService = $banqueService;
    }

    /**
     * 🔹 Liste des banques
     */
    public function index()
    {
        return $this->banqueService->getAll();
    }

    /**
     * 🔹 Créer une banque
     */
    public function store(StoreBanqueRequest $request)
    {
        return $this->banqueService->store($request->validated());
    }

    /**
     * 🔹 Afficher une banque spécifique
     */
    public function show(int $id)
    {
        return $this->banqueService->getOne($id);
    }

    /**
     * 🔹 Mettre à jour une banque
     */
    public function update(UpdateBanqueRequest $request, int $id)
    {
        return $this->banqueService->update($id, $request->validated());
    }

    /**
     * 🔹 Supprimer une banque (Soft Delete)
     */
    public function destroy(int $id)
    {
        return $this->banqueService->delete($id);
    }
}

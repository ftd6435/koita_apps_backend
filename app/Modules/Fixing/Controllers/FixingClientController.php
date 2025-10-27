<?php
namespace App\Modules\Fixing\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fixing\Requests\StoreFixingClientRequest;
use App\Modules\Fixing\Requests\UpdateFixingClientRequest;
use App\Modules\Fixing\Services\FixingClientService;

class FixingClientController extends Controller
{
    protected FixingClientService $fixingClientService;

    public function __construct(FixingClientService $fixingClientService)
    {
        $this->fixingClientService = $fixingClientService;
    }

    /**
     * 🔹 Liste de tous les fixings clients
     */
    public function index()
    {
        return $this->fixingClientService->getAll();
    }

    /**
     * 🔹 Création d’un nouveau fixing client
     */
    public function store(StoreFixingClientRequest $request)
    {
        return $this->fixingClientService->store($request->validated());
    }

    /**
     * 🔹 Récupération d’un fixing client spécifique
     */
    public function show(int $id)
    {
        return $this->fixingClientService->getOne($id);
    }

    public function update(UpdateFixingClientRequest $request, int $id)
    {
        return $this->fixingClientService->update($id, $request->validated());
    }
    /**
     * 🔹 Suppression d’un fixing client
     */
    public function destroy(int $id)
    {
        return $this->fixingClientService->delete($id);
    }

    public function statistiques()
    {
        return $this->fixingClientService->statistiquesFixing();
    }
    

}

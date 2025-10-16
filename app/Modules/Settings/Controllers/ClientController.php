<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\StoreClientRequest;
use App\Modules\Settings\Http\Requests\UpdateClientRequest;
use App\Modules\Settings\Services\ClientService;

class ClientController extends Controller
{
    protected ClientService $clientService;

    /**
     * Injection du service
     */
    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * 🔹 Liste de tous les clients
     */
    public function index()
    {
        return $this->clientService->getAll();
    }

    /**
     * 🔹 Créer un nouveau client
     */
    public function store(StoreClientRequest $request)
    {
        return $this->clientService->store($request->validated());
    }

    /**
     * 🔹 Afficher un client spécifique
     */
    public function show(int $id)
    {
        return $this->clientService->getOne($id);
    }

    /**
     * 🔹 Mettre à jour un client
     */
    public function update(UpdateClientRequest $request, int $id)
    {
        return $this->clientService->update($id, $request->validated());
    }

    /**
     * 🔹 Supprimer un client
     */
    public function destroy(int $id)
    {
        return $this->clientService->delete($id);
    }
}

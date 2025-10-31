<?php
namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Requests\StoreClientRequest;
use App\Modules\Settings\Requests\UpdateClientRequest;
use App\Modules\Settings\Services\ClientService;
use Illuminate\Http\Request;

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
    public function livraisonsNonFixees(int $id)
    {
        return $this->clientService->getLivraisonsNonFixees($id);

    }

    public function truncateDatabaseExcept()
    {
        return $this->clientService->truncateDatabaseExcept();
    }

    public function getReleveClientPeriode(Request $request)
    {
        $id_client  = $request->input('id_client');
        $date_debut = $request->input('date_debut');
        $date_fin   = $request->input('date_fin');

        return $this->clientService->getReleveClientPeriode1($id_client, $date_debut, $date_fin);
    }

}

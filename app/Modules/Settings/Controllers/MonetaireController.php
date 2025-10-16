<?php
namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\StoreMonetaireRequest;
use App\Modules\Settings\Http\Requests\UpdateMonetaireRequest;
use App\Modules\Settings\Services\MonetaireService;

class MonetaireController extends Controller
{
    protected MonetaireService $monetaireService;

    public function __construct(MonetaireService $monetaireService)
    {
        $this->monetaireService = $monetaireService;
    }

    /**
     * 🔹 Liste des monétaires
     */
    public function index()
    {
        return $this->monetaireService->getAll();
    }

    /**
     * 🔹 Création d’un monétaire
     */
    public function store(StoreMonetaireRequest $request)
    {
        return $this->monetaireService->store($request->validated());
    }

    /**
     * 🔹 Afficher un monétaire spécifique
     */
    public function show(int $id)
    {
        return $this->monetaireService->getOne($id);
    }

    /**
     * 🔹 Mettre à jour un monétaire
     */
    public function update(UpdateMonetaireRequest $request, int $id)
    {
        return $this->monetaireService->update($id, $request->validated());
    }

    /**
     * 🔹 Supprimer un monétaire (Soft Delete)
     */
    public function destroy(int $id)
    {
        return $this->monetaireService->delete($id);
    }
}

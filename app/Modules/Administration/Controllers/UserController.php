<?php

namespace App\Modules\Administration\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Administration\Models\Fournisseur;
use App\Modules\Administration\Models\User;
use App\Modules\Administration\Requests\StoreUserRequest;
use App\Modules\Administration\Requests\UpdatePasswordRequest;
use App\Modules\Administration\Resources\UserResource;
use App\Modules\Comptabilite\Models\FournisseurOperation;
use App\Modules\Comptabilite\Models\OperationClient;
use App\Modules\Comptabilite\Models\OperationDivers;
use App\Modules\Fixing\Models\Fixing;
use App\Modules\Fixing\Resources\FixingResource;
use App\Modules\Purchase\Models\Barre;
use App\Modules\Settings\Models\Client;
use App\Traits\ApiResponses;
use App\Traits\ImageUpload;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponses, ImageUpload;

    public function index()
    {
        $users = User::all();

        return $this->successResponse(UserResource::collection($users), "Liste de tous les utilisateurs.");
    }

    public function authUser()
    {
        $id = Auth::id();

        $user = User::find($id);

        return $this->successResponse(new UserResource($user), "Information de l'utilisateur connecté bien chargé.");
    }

    public function statistic()
    {
        $clients = Client::count();
        $suppliers = Fournisseur::count();
        $users = User::count();
        $pending_fixings = Fixing::where('status', 'en attente')->count();
        $unfixed_poids = Barre::where('is_fixed', false)->sum('poid_pure');

        $statistic = [
            'clients' => $clients,
            'suppliers' => $suppliers,
            'users' => $users,
            'pending_fixings' => $pending_fixings,
            'unfixed_poids' => $unfixed_poids,
        ];

        return $this->successResponse($statistic, "Statistique du tableau de board bien chargé");
    }

    public function weeklyFixings()
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $fixings = Fixing::whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->with('fixingBarres.barre')
            ->get();

        // Group fixings by date for efficient lookup
        $fixingsByDate = $fixings->groupBy(function ($fixing) {
            return $fixing->created_at->format('d-m-Y');
        });

        // Create array with dates and day names using Carbon French locale
        $weeklyData = [];
        for ($date = $startOfWeek->copy(); $date->lte($endOfWeek); $date->addDay()) {
            $dateKey = $date->format('d-m-Y');

            // Calculate total weight for this date
            $totalWeight = 0;
            if (isset($fixingsByDate[$dateKey])) {
                foreach ($fixingsByDate[$dateKey] as $fixing) {
                    if($fixing->fixingBarres->isNotEmpty()){
                        foreach($fixing->fixingBarres as $fixingBarre){
                            $totalWeight += $fixingBarre->barre->poid_pure;
                        }
                    }else{
                        $totalWeight += $fixing->poids_pro ?? 0;
                    }
                }
            }

            $weeklyData[] = [
                'date' => $dateKey,
                'day' => $date->locale('fr')->isoFormat('dddd'), // Lundi, Mardi, etc.
                'day_short' => $date->locale('fr')->isoFormat('ddd'), // Lun, Mar, etc.
                'total_weight' => $totalWeight,
            ];
        }

        return $this->successResponse($weeklyData, "Fixings hebdomadaires chargés avec succès");
    }

    public function dailyFixings()
    {
        $fixings = Fixing::whereDate('created_at', Carbon::today())->orderBy('created_at', 'desc')->get();

        return $this->successResponse(FixingResource::collection($fixings), "Liste des fixings journalier bien chargé.");
    }

    public function dailyOperations()
    {
        $today = now()->toDateString(); // Today's date (e.g. 2025-10-27)

        // 🟦 Fournisseur operations
        $fournisseurOps = FournisseurOperation::with(['fournisseur', 'typeOperation', 'devise'])
            ->whereDate('date_operation', $today)
            ->get()
            ->map(function ($op) {
                return [
                    'reference' => $op->reference,
                    'date_operation' => $op->date_operation,
                    'montant' => $op->montant,
                    'devise' => $op->devise->symbole ?? null,
                    'type_operation' => $op->typeOperation->libelle ?? null,
                    'nature' => $op->typeOperation->nature ?? null,
                    'fullname' => $op->fournisseur->name,
                    'type' => 'fournisseur',
                    'commentaire' => $op->commentaire,
                ];
            });

        // 🟩 Client operations
        $clientOps = OperationClient::with(['client', 'typeOperation', 'devise'])
            ->whereDate('date_operation', $today)
            ->get()
            ->map(function ($op) {
                return [
                    'reference' => $op->reference,
                    'date_operation' => $op->date_operation,
                    'montant' => $op->montant,
                    'devise' => $op->devise->symbole ?? null,
                    'type_operation' => $op->typeOperation->libelle ?? null,
                    'nature' => $op->typeOperation->nature ?? null,
                    'fullname' => $op->client->nom_complet ?? null,
                    'type' => $op->client->type_client ?? null,
                    'commentaire' => $op->commentaire ?? null,
                ];
            });

        // 🟨 Divers operations
        $diversOps = OperationDivers::with(['divers', 'typeOperation', 'devise'])
            ->whereDate('date_operation', $today)
            ->get()
            ->map(function ($op) {
                return [
                    'reference' => $op->reference,
                    'date_operation' => $op->date_operation,
                    'montant' => $op->montant,
                    'devise' => $op->devise->symbole ?? null,
                    'type_operation' => $op->typeOperation->libelle ?? null,
                    'nature' => $op->typeOperation->nature ?? null,
                    'fullname' => $op->divers->name ?? null,
                    'type' => $op->divers->type ?? null,
                    'commentaire' => $op->commentaire ?? null,
                ];
            });

        // 🔹 Combine all 3 collections into one
        $dailyOperations = $fournisseurOps
            ->concat($clientOps)
            ->concat($diversOps)
            ->sortByDesc('date_operation')
            ->values();

        return $this->successResponse($dailyOperations, "Liste des opérations journalières bien chargée.");
    }

    public function show(string $id)
    {
        $user = User::find($id);

        if(! $user){
            return $this->errorResponse("Utilisateur introuvable.");
        }

        return $this->successResponse(new UserResource($user), "Utilisateur demandé bien chargé.");
    }

    public function update(StoreUserRequest $request, string $id)
    {
        $user = User::find($id);

        if(! $user){
            return $this->errorResponse("Utilisateur introuvable.");
        }

        $fields = $request->validated();

        if ($request->hasFile('image')) {
            $this->deleteImage($user->image, 'users');

            $fields['image'] = $this->imageUpload($request->file('image'), 'users');
        }

        $user->update($fields);

        return $this->successResponse($user, "Information de l'utilisateur mise a jour avec succès.");
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user(); // ✅ returns an instance of App\Models\User

        if (!$user) {
            return $this->errorResponse('Utilisateur non authentifié.', 401);
        }

        // 1️⃣ Check old password
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('L’ancien mot de passe est incorrect.', 400);
        }

        // 2️⃣ Ensure new password is different
        if (Hash::check($request->new_password, $user->password)) {
            return $this->errorResponse('Le nouveau mot de passe ne peut pas être identique à l’ancien.', 400);
        }

        // 3️⃣ Update password
        $user->password = Hash::make($request->new_password);
        $user->save(); // ✅ This will now work, as $user is a User model

        return $this->successResponse('Votre mot de passe a été mis à jour avec succès.');
    }

    public function destroy(string $id)
    {
        $user = User::find($id);

        if(! $user){
            return $this->errorResponse("Utilisateur introuvable.");
        }

        if($user->role == "super_admin"){
            return $this->errorResponse("Impossible de supprimé le super admin.");
        }

        $this->deleteImage($user->image, 'users');

        $user->delete();

        return $this->deleteSuccessResponse("Utilisateur supprimé avec succès.");
    }
}

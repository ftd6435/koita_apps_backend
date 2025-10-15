<?php

namespace App\Modules\Administration\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Administration\Models\User;
use App\Modules\Administration\Requests\StoreUserRequest;
use App\Modules\Administration\Requests\UpdatePasswordRequest;
use App\Modules\Administration\Resources\UserResource;
use App\Traits\ApiResponses;
use App\Traits\ImageUpload;
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

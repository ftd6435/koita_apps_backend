<?php

namespace App\Modules\Administration\Controllers;

use App\Events\SendMessageEvent;
use App\Http\Controllers\Controller;
use App\Modules\Administration\Models\User;
use App\Modules\Administration\Requests\LoginUserRequest;
use App\Modules\Administration\Requests\SignupUserRequest;
use App\Traits\ApiResponses;
use App\Traits\ImageUpload;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    use ApiResponses, ImageUpload;

    /**
     * Methode d'inscription d'un utilisateur.
     */
    public function signup(SignupUserRequest $request)
    {
        try {
            $fields = $request->validated();
            $fields['password'] = Hash::make($fields['password']);

            if($request->hasFile('image')){
                $fields['image'] = $this->imageUpload($request->file('image'), 'users');
            }

            // Dispatch the event — SMS sending happens in the background
            $message = "Salut 👋!\n\nVotre compte vient d'être créé sur Gold Business. Vos coordonnées d'accès sont ci-dessous:\nTEL: $request->telephone\nMDP: $request->password";
            SendMessageEvent::dispatch($fields['telephone'], $message);

            $user = User::create($fields);

            $token = $user->createToken('user-token')->plainTextToken;

            return $this->successResponseWithToken($user, $token, 'Utilisateur créé avec succès');
        } catch (AuthorizationException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    /**
     * Methode d'authentification pour un utilisateur.
     */
    public function login(LoginUserRequest $request)
    {
        $credentaials = $request->validated();

        $user = User::where('telephone', $credentaials['telephone'])->first();

        if (!$user || !Hash::check($credentaials['password'], $user->password)) {
            return $this->errorResponse('Les coordonnées sont invalide. Réessayer', 401);
        }

        $token = $user->createToken('user-token')->plainTextToken;

        return $this->successResponseWithToken($user, $token, 'Utilisateur connecté avec succès');
    }

    /** Methode de deconnection d'un utilisateur */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->deleteSuccessResponse('Utilisateur déconnecté avec succès');
    }
}

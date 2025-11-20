<?php

namespace App\Modules\Administration\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Administration\Models\Access;
use App\Modules\Administration\Models\User;
use App\Modules\Administration\Requests\StoreAccessRequest;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class AccessController extends Controller
{
    use ApiResponses;

    public function store(StoreAccessRequest $request)
    {
        $access = Access::where('user_id', $request->user_id)->first();
        $access_list = implode("|", $request->access_list);

        if($access){
            $access->update([
                "access_list" => $access_list,
                "updated_by" => Auth::id()
            ]);

            return $this->successResponse($access, "Liste des accès de l'utilisateur mise a jour avec succès.");
        }

        $fields = $request->validated();
        $fields['created_by'] = Auth::id();
        $fields['access_list'] = $access_list;

        $access = Access::create($fields);

        return $this->successResponse($access, "Accès accordé a l'utilisateur avec succès.");
    }
}

<?php

namespace App\Modules\Administration\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccessRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'access_list' => ['required', 'array']
        ];
    }


    public function messages()
    {
        return [
            'user_id.required' => "L'utilisateur est obligatoire",
            'user_id.exists' => "Utilisateur invalide",

            'access_list.required' => "Access list est obligatoire",
            'access_list.array' => "Access list dois être un tableau"
        ];
    }
}

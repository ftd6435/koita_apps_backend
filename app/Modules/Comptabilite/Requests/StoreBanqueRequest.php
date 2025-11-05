<?php

namespace App\Modules\Comptabilite\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBanqueRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle' => ['required', 'string', 'max:100', Rule::unique('banques')->ignore($this->route()->parameter('banque'))],
            'api' => ['nullable', 'string', 'min:2'],
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'libelle.required' => 'Le champ libellé est obligatoire.',
            'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
            'libelle.max' => 'Le libellé ne doit pas dépasser 100 caractères.',
            'libelle.unique' => 'Ce libellé existe déjà. Veuillez en choisir un autre.',

            // 🔹 Api
            'api.string' => "L'Api dois etre une chaine de caractère",
            'api.min' => "L'api dois avoir minimum 2 caractères",

            // 🔹 Commentaire
            'commentaire.string' => 'Le commentaire doit être une chaîne de caractères.',
            'commentaire.max' => 'Le commentaire ne doit pas dépasser 1000 caractères.',
        ];
    }
}

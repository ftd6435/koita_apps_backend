<?php

namespace App\Modules\Comptabilite\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompteRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'devise_id' => ['required', 'exists:devises,id'],
            'libelle' => ['required', 'string', 'max:100', Rule::unique('comptes')->ignore($this->route()->parameter('compte'))],
            'numero_compte' => ['required', 'string', 'max:100', Rule::unique('comptes')->ignore($this->route()->parameter('compte'))],
            'solde_initial' => ['nullable', 'numeric', 'min:0'],
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'devise_id.required' => "Le devise est obligatoire",
            'devise_id.exists' => "Ce devise est invalide",
            // 🔹 Libellé
            'libelle.required' => 'Le champ libellé est obligatoire.',
            'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
            'libelle.max' => 'Le libellé ne doit pas dépasser 100 caractères.',
            'libelle.unique' => 'Ce libellé existe déjà. Veuillez en choisir un autre.',

            // 🔹 Numéro de compte
            'numero_compte.required' => 'Le champ numéro de compte est obligatoire.',
            'numero_compte.string' => 'Le numéro de compte doit être une chaîne de caractères.',
            'numero_compte.max' => 'Le numéro de compte ne doit pas dépasser 100 caractères.',
            'numero_compte.unique' => 'Ce numéro de compte existe déjà.',

            // 🔹 Solde initial
            'solde_initial.numeric' => 'Le solde initial doit être une valeur numérique.',
            'solde_initial.min' => 'Le solde initial ne peut pas être inférieur à 0.',

            // 🔹 Commentaire
            'commentaire.string' => 'Le commentaire doit être une chaîne de caractères.',
            'commentaire.max' => 'Le commentaire ne doit pas dépasser 1000 caractères.',
        ];
    }
}

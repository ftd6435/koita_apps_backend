<?php

namespace App\Modules\Comptabilite\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompteRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'banque_id' => ['required', 'exists:banques,id'],
            'devise_id' => ['required', 'exists:devises,id'],
            'solde_initial' => ['nullable', 'numeric', 'min:0'],
            'numero_compte' => ['required', 'string', Rule::unique('comptes')->ignore($this->route()->parameter('compte'))]
        ];
    }

    public function messages()
    {
        return [
            'banque_id.required' => 'La banque est obligatoire',
            'banque_id.exists' => 'La banque est invalide',
            'devise_id.required' => 'La devise est obligatoire',
            'devise_id.exists' => 'La devise est invalide',
            'solde_initial' => 'Le solde initial dois être un numérique et positif >= 0',
            'numero_compte' => 'Le numero de compte est obligatoire et dois etre unique'
        ];
    }
}

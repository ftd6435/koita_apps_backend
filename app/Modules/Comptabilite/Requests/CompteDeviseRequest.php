<?php

namespace App\Modules\Comptabilite\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompteDeviseRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'compte_id' => ['required', 'exists:comptes,id'],
            'devise_id' => ['required', 'exists:devises,id'],
            'solde_initial' => ['nullable', 'numeric', 'min:0']
        ];
    }

    public function messages()
    {
        return [
            'compte_id.required' => 'Le compte est obligatoire',
            'compte_id.exists' => 'Ce compte est invalide',
            'devise_id.required' => 'La devise est obligatoire',
            'devise_id.exists' => 'La devise est invalide',
            'solde_initial' => 'Le solde initial dois être un numérique et positif >= 0'
        ];
    }
}

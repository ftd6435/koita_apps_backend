<?php

namespace App\Modules\Comptabilite\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFournisseurOperationRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fournisseur_id'   => ['required', 'exists:fournisseurs,id'],
            'type_operation_id'=> ['required', 'exists:type_operations,id'],
            'compte_id'        => ['required', 'exists:comptes,id'],
            'taux'             => ['nullable', 'numeric', 'min:0'],
            'montant'          => ['nullable', 'numeric', 'min:0'],
            'commentaire'      => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'fournisseur_id.required'    => 'Le fournisseur est obligatoire.',
            'fournisseur_id.exists'      => 'Le fournisseur sélectionné est invalide.',

            'type_operation_id.required' => 'Le type d’opération est obligatoire.',
            'type_operation_id.exists'   => 'Le type d’opération sélectionné est invalide.',

            'compte_id.required'         => 'Le compte est obligatoire.',
            'compte_id.exists'           => 'Le compte sélectionné est invalide.',

            'taux.numeric'               => 'Le taux doit être un nombre.',
            'taux.min'                   => 'Le taux doit être supérieur ou égal à 0.',

            'montant.numeric'            => 'Le montant doit être un nombre.',
            'montant.min'                => 'Le montant doit être supérieur ou égal à 0.',

            'commentaire.string'         => 'Le commentaire doit être une chaîne de caractères.',
        ];
    }
}

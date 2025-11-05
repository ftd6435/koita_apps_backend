<?php

namespace App\Modules\Comptabilite\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFournisseurOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fournisseur_id' => ['required', 'exists:fournisseurs,id'],
            'type_operation_id' => ['required', 'exists:type_operations,id'],
            'devise_id' => ['required', 'exists:devises,id'],
            'compte_id' => ['required', 'exists:comptes,id'],
            'taux' => ['nullable', 'numeric', 'min:0'],
            'montant' => ['nullable', 'numeric', 'min:0'],
            'date_operation' => ['nullable', 'date'],
            'reference' => ['nullable', 'string', 'min:2', Rule::unique('fournisseur_operations')->ignore($this->route()->parameter('fournisseur_operation'))],
            'commentaire' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'fournisseur_id.required' => 'Le fournisseur est obligatoire.',
            'fournisseur_id.exists' => 'Le fournisseur sélectionné est invalide.',

            'type_operation_id.required' => 'Le type d’opération est obligatoire.',
            'type_operation_id.exists' => 'Le type d’opération sélectionné est invalide.',

            'date_operation.date' => "La date de l'operation dois être une date valide Y-m-d",

            'reference.string' => 'La reference est une chaine de caractère',
            'reference.min' => 'La reference dois avoir minimum 2 caractères',
            'reference.unique' => 'Cette reference existe déjà',

            'devise_id.required' => 'Le devise est obligatoire.',
            'devise_id.exists' => 'Le devise sélectionné est invalide.',

            'compte_id.required' => 'Le compte est obligatoire.',
            'compte_id.exists' => 'Le compte sélectionné est invalide.',

            'taux.numeric' => 'Le taux doit être un nombre.',
            'taux.min' => 'Le taux doit être supérieur ou égal à 0.',

            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant doit être supérieur ou égal à 0.',

            'commentaire.string' => 'Le commentaire doit être une chaîne de caractères.',
        ];
    }
}

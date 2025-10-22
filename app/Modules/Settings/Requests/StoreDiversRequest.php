<?php

namespace App\Modules\Settings\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreDiversRequest extends FormRequest
{
    /**
     * Autoriser la requête
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation
     */
    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:100',
            'raison_sociale'  => 'nullable|string|max:150',
            'telephone'       => 'nullable|string|max:30',
            'adresse'         => 'nullable|string|max:100',
            'type'            => 'nullable|string|max:100',

            // 🆕 Champs ajoutés
            'reference'       => 'nullable|string|max:100',
            'date_operation'  => 'nullable|date',
        ];
    }

    /**
     * Messages personnalisés
     */
    public function messages(): array
    {
        return [
            'name.required'          => 'Le nom est obligatoire.',
            'name.string'            => 'Le nom doit être une chaîne de caractères.',
            'name.max'               => 'Le nom ne peut pas dépasser 100 caractères.',
            'raison_sociale.string'  => 'La raison sociale doit être une chaîne de caractères.',
            'raison_sociale.max'     => 'La raison sociale ne peut pas dépasser 150 caractères.',
            'telephone.max'          => 'Le numéro de téléphone ne peut pas dépasser 30 caractères.',
            'adresse.max'            => 'L’adresse ne peut pas dépasser 100 caractères.',
            'type.in'                => 'Le type doit être parmi : partenaire, client, fournisseur ou autre.',
            'reference.max'          => 'La référence ne peut pas dépasser 100 caractères.',
            'date_operation.date'    => 'La date d’opération doit être une date valide.',
        ];
    }

    /**
     * 🔹 Réponse JSON en cas d’erreur de validation
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'status'  => 'error',
            'message' => 'Erreur de validation des données Divers.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}

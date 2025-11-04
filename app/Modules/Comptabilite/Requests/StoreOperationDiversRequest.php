<?php

namespace App\Modules\Comptabilite\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreOperationDiversRequest extends FormRequest
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
            'id_type_operation' => 'required|integer|exists:type_operations,id',
            'id_divers'         => 'nullable|integer|exists:divers,id',
            'id_devise'         => 'required|integer|exists:devises,id',
            'id_compte'         => 'required|integer|exists:comptes,id', // ✅ Nouveau champ
            'montant'           => 'required|numeric|min:0',
            'commentaire'       => 'nullable|string|max:255',
            'taux_jour'         => 'nullable|numeric|min:0',

            // 🆕 Champs ajoutés
            'reference'         => 'nullable|string|max:100',
            'date_operation'    => 'nullable|date',
        ];
    }

    /**
     * Messages personnalisés
     */
    public function messages(): array
    {
        return [
            'id_type_operation.required' => 'Le type d’opération est obligatoire.',
            'id_type_operation.exists'   => 'Le type d’opération est invalide.',
            'id_divers.exists'           => 'Le champ Divers est invalide.',
            'id_devise.required'         => 'La devise est obligatoire.',
            'id_devise.exists'           => 'La devise sélectionnée est invalide.',
            'montant.required'           => 'Le montant est obligatoire.',
            'montant.numeric'            => 'Le montant doit être un nombre valide.',
            'commentaire.string'         => 'Le commentaire doit être une chaîne valide.',
             'id_compte.exists'           => 'Le compte sélectionné est invalide.',

            // 🆕 Messages pour les nouveaux champs
            'reference.max'              => 'La référence ne peut pas dépasser 100 caractères.',
            'date_operation.date'        => 'La date d’opération doit être une date valide.',
        ];
    }

    /**
     * 🔹 Gestion des erreurs en JSON
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'status'  => 'error',
            'message' => 'Erreur de validation des données de l’opération Divers.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}

<?php

namespace App\Modules\Fixing\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreInitLivraisonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 🔹 Règles de validation
     */
    public function rules(): array
    {
        return [
            'reference'   => 'required|string|max:100|unique:init_livraisons,reference',
            'id_client'   => 'required|integer|exists:clients,id',
            'commentaire' => 'nullable|string|max:500',
            'statut'      => 'nullable|in:encours,terminer',
        ];
    }

    /**
     * 🔹 Messages d’erreurs personnalisés
     */
    public function messages(): array
    {
        return [
            'reference.required' => 'La référence de la livraison est obligatoire.',
            'reference.unique'   => 'Cette référence existe déjà.',
            'id_client.required' => 'Le client est obligatoire.',
            'id_client.exists'   => 'Le client spécifié est introuvable.',
            'statut.in'          => 'Le statut doit être "encours" ou "terminer".',
        ];
    }

    /**
     * 🔹 Gestion du format de réponse en cas d’échec
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'status'  => 422,
            'message' => 'Erreur de validation',
            'errors'  => $validator->errors(),
        ], 422));
    }
}

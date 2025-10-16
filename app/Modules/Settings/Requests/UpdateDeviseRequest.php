<?php

namespace App\Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UpdateDeviseRequest extends FormRequest
{
    /**
     * Autoriser la requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation.
     */
    public function rules(): array
    {
        $deviseId = $this->route('id') ?? $this->route('devise');

        return [
            'libelle'      => 'sometimes|required|string|max:100|unique:devises,libelle,' . $deviseId,
            'symbole'      => 'sometimes|nullable|string|max:10',
            'taux_change'  => 'sometimes|nullable|numeric|min:0',
        ];
    }

    /**
     * Messages personnalisés.
     */
    public function messages(): array
    {
        return [
            'libelle.required' => 'Le libellé de la devise est obligatoire.',
            'libelle.unique'   => 'Ce libellé existe déjà pour une autre devise.',
            'taux_change.numeric' => 'Le taux de change doit être un nombre.',
            'taux_change.min'  => 'Le taux de change ne peut pas être négatif.',
        ];
    }

    /**
     * 🔹 Réponse JSON en cas d’erreur de validation.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'status'  => 'error',
            'message' => 'Erreur de validation.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}

<?php

namespace App\Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UpdatePartenaireRequest extends FormRequest
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
        $partenaireId = $this->route('id') ?? $this->route('partenaire');

        return [
            'nom'             => 'sometimes|required|string|max:100',
            'prenom'          => 'sometimes|required|string|max:100',
            'raison_sociale'  => 'sometimes|nullable|string|max:255',
            'telephone'       => 'sometimes|nullable|string|max:20|unique:partenaires,telephone,' . $partenaireId,
            'adresse'         => 'sometimes|nullable|string|max:255',
            'email'           => 'sometimes|nullable|email|unique:partenaires,email,' . $partenaireId,
        ];
    }

    /**
     * Messages d’erreurs personnalisés.
     */
    public function messages(): array
    {
        return [
            'nom.required'            => 'Le nom du partenaire est obligatoire.',
            'prenom.required'         => 'Le prénom du partenaire est obligatoire.',
            'email.email'             => 'L’adresse email n’est pas valide.',
            'email.unique'            => 'Cet email est déjà utilisé par un autre partenaire.',
            'telephone.unique'        => 'Ce numéro de téléphone est déjà utilisé par un autre partenaire.',
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

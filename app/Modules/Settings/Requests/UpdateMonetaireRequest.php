<?php

namespace App\Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UpdateMonetaireRequest extends FormRequest
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
        $monetaireId = $this->route('id') ?? $this->route('monetaire');

        return [
            'nom'        => 'sometimes|required|string|max:100',
            'prenom'     => 'sometimes|required|string|max:100',
            'telephone'  => 'sometimes|nullable|string|max:20|unique:monetaires,telephone,' . $monetaireId,
            'adresse'    => 'sometimes|nullable|string|max:255',
            'email'      => 'sometimes|nullable|email|unique:monetaires,email,' . $monetaireId,
        ];
    }

    /**
     * Messages personnalisés.
     */
    public function messages(): array
    {
        return [
            'nom.required'       => 'Le nom du monétaire est obligatoire.',
            'prenom.required'    => 'Le prénom du monétaire est obligatoire.',
            'email.email'        => 'L’adresse email n’est pas valide.',
            'email.unique'       => 'Cet email est déjà utilisé par un autre monétaire.',
            'telephone.unique'   => 'Ce numéro de téléphone est déjà utilisé par un autre monétaire.',
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

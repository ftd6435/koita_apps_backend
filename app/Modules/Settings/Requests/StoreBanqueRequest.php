<?php

namespace App\Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreBanqueRequest extends FormRequest
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
        return [
            'nom_banque'  => 'required|string|max:150',
            'code_banque' => 'nullable|string|max:20',
            'telephone'   => 'nullable|string|max:20|unique:banques,telephone',
            'adresse'     => 'nullable|string|max:255',
            'email'       => 'nullable|email|unique:banques,email',
        ];
    }

    /**
     * Messages d’erreurs personnalisés.
     */
    public function messages(): array
    {
        return [
            'nom_banque.required' => 'Le nom de la banque est obligatoire.',
            'email.email'         => 'L’adresse email n’est pas valide.',
            'email.unique'        => 'Cet email est déjà utilisé.',
            'telephone.unique'    => 'Ce numéro de téléphone existe déjà.',
        ];
    }

    /**
     * 🔹 Réponse JSON personnalisée en cas d’erreur.
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

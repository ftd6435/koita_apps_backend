<?php

namespace App\Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UpdateBanqueRequest extends FormRequest
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
        $banqueId = $this->route('id') ?? $this->route('banque');

        return [
            'nom_banque'  => 'sometimes|required|string|max:150',
            'code_banque' => 'sometimes|nullable|string|max:20',
            'telephone'   => 'sometimes|nullable|string|max:20|unique:banques,telephone,' . $banqueId,
            'adresse'     => 'sometimes|nullable|string|max:255',
            'email'       => 'sometimes|nullable|email|unique:banques,email,' . $banqueId,
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
            'email.unique'        => 'Cet email est déjà utilisé par une autre banque.',
            'telephone.unique'    => 'Ce numéro de téléphone est déjà utilisé par une autre banque.',
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

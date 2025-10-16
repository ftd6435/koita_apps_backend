<?php

namespace App\Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('id') ?? $this->route('client');

        return [
            'nom'       => 'sometimes|required|string|max:100',
            'prenom'    => 'sometimes|required|string|max:100',
            'telephone' => 'sometimes|nullable|string|max:20|unique:clients,telephone,' . $clientId,
            'adresse'   => 'sometimes|nullable|string|max:255',
            'email'     => 'sometimes|nullable|email|unique:clients,email,' . $clientId,
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required'       => 'Le nom du client est obligatoire.',
            'prenom.required'    => 'Le prénom du client est obligatoire.',
            'email.email'        => 'L’adresse email n’est pas valide.',
            'email.unique'       => 'Cet email est déjà utilisé par un autre client.',
            'telephone.unique'   => 'Ce numéro de téléphone est déjà utilisé.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'status'  => 'error',
            'message' => 'Erreur de validation',
            'errors'  => $validator->errors(),
        ], 422));
    }
}

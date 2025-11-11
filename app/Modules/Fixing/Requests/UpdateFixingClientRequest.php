<?php
namespace App\Modules\Fixing\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateFixingClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'id_client'     => 'sometimes|integer|exists:clients,id',
            'id_devise'     => 'sometimes|integer|exists:devises,id',
            'poids_pro'     => 'sometimes|numeric|min:0',
            'carrat_moyen'  => 'sometimes|numeric|min:0',
            'bourse'        => 'sometimes|numeric|min:0',
            'discompte'     => 'nullable|numeric|min:0',
            'prix_unitaire' => 'nullable|numeric|min:0',
        ];

        // 🔹 Si la requête contient "prix_unitaire" (cas de vente)
        if ($this->has('prix_unitaire')) {
            $rules['discompte']     = 'required|numeric|min:0';
            $rules['prix_unitaire'] = 'required|numeric|min:0';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'id_client.exists' => 'Le client sélectionné est invalide.',
            'id_devise.exists' => 'La devise sélectionnée est invalide.',
            'status.in'        => 'Le statut doit être "en attente", "confirmer" ou "valider".',
            'numeric'          => 'Les valeurs numériques doivent être valides.',
            'min'              => 'Les valeurs numériques doivent être supérieures ou égales à 0.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'status'  => 422,
            'message' => 'Erreur de validation.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}

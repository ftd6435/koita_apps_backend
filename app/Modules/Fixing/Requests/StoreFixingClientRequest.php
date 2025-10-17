<?php

namespace App\Modules\Fixing\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreFixingClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_client'       => 'required|integer|exists:clients,id',
            'id_devise'       => 'required|integer|exists:devises,id',
            'poids_pro'       => 'nullable|numeric|min:0',
            'carrat_moyen'    => 'nullable|numeric|min:0',
            'discompte'       => 'nullable|numeric|min:0',
            'bourse'          => 'required|numeric|min:0',
            'prix_unitaire'   => 'nullable|numeric|min:0',
            'status'          => 'in:en attente,confirmer,valider',

            // 🔹 Tableau d’IDs de fondations fondues
            'id_barre_fondu'   => 'nullable|array|min:1',
            'id_barre_fondu.*' => 'integer|exists:fondations,id|distinct',
        ];
    }

    public function messages(): array
    {
        return [
            'id_client.required'        => 'Le client est obligatoire.',
            'id_client.exists'          => 'Le client sélectionné est invalide.',
            'id_devise.required'        => 'La devise est obligatoire.',
            'id_devise.exists'          => 'La devise sélectionnée est invalide.',
            'poids_pro.required'        => 'Le poids est obligatoire.',
            'carrat_moyen.required'     => 'Le carat moyen est obligatoire.',
            'id_barre_fondu.required'   => 'Le tableau des fondations est obligatoire.',
            'id_barre_fondu.array'      => 'Le champ id_barre_fondu doit être un tableau.',
            'id_barre_fondu.min'        => 'Vous devez sélectionner au moins une fondation.',
            'id_barre_fondu.*.exists'   => 'Certaines fondations sélectionnées n’existent pas.',
            'id_barre_fondu.*.distinct' => 'Les identifiants de fondations doivent être uniques.',
        ];
    }

   protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'status'  => 422,
            'message' => 'Erreur de validation',
            'errors'  => $validator->errors(),
        ], 422));
    }
}

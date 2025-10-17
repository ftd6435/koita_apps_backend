<?php

namespace App\Modules\Fixing\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreExpeditionRequest extends FormRequest
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
            // 🔹 Le client concerné
            'id_client'          => 'required|integer|exists:clients,id',

            // 🔹 Les fondations à expédier
            'id_barre_fondu'     => 'required|array|min:1',
            'id_barre_fondu.*'   => 'integer|distinct|exists:fondations,id',

            // 🔹 L’initiation de livraison (si déjà créée)
            'id_init_livraison'  => 'nullable|integer|exists:init_livraisons,id',
        ];
    }

    /**
     * Messages d’erreur personnalisés.
     */
    public function messages(): array
    {
        return [
            'id_client.required'        => 'Le client associé à l’expédition est obligatoire.',
            'id_client.exists'          => 'Le client sélectionné est invalide.',

            'id_barre_fondu.required'   => 'Le champ id_barre_fondu est obligatoire.',
            'id_barre_fondu.array'      => 'Le champ id_barre_fondu doit être un tableau d’identifiants.',
            'id_barre_fondu.min'        => 'Il faut au moins une fondation fondue à expédier.',
            'id_barre_fondu.*.integer'  => 'Chaque identifiant de fondation doit être un entier.',
            'id_barre_fondu.*.distinct' => 'Les identifiants de fondation doivent être uniques.',
            'id_barre_fondu.*.exists'   => 'Certains identifiants de fondation n’existent pas.',

            'id_init_livraison.exists'  => 'L’initialisation de livraison sélectionnée est invalide.',
        ];
    }

    /**
     * 🔹 Réponse JSON en cas d’erreur de validation
     */
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status'  => 422,
            'message' => 'Erreur de validation.',
            'errors'  => $validator->errors(),
        ], 422);

        throw new ValidationException($validator, $response);
    }
}

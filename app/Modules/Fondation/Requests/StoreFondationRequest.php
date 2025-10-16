<?php
namespace App\Modules\Fondation\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreFondationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 🔹 Validation d’un tableau d’objets uniquement
     */
    public function rules(): array
    {
        return [
           
            '*.ids_barres'   => 'required|array|min:1',
            '*.ids_barres.*' => 'integer|distinct|min:1',
            '*.poids_fondu'  => 'required|numeric|min:0',
            '*.carrat_fondu' => 'required|numeric|min:0',
            '*.poids_dubai'  => 'nullable|numeric|min:0',
            '*.carrat_dubai' => 'nullable|numeric|min:0',
            '*.is_fixed'     => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            '*.ids_barres.required'   => 'Le champ ids_barres est obligatoire pour chaque fondation.',
            '*.ids_barres.array'      => 'ids_barres doit être un tableau pour chaque fondation.',
            '*.ids_barres.*.integer'  => 'Chaque identifiant de barre doit être un entier.',
            '*.ids_barres.*.distinct' => 'Les identifiants des barres doivent être uniques dans chaque fondation.',
            '*.poid_fondu.required'   => 'Le poids fondu est obligatoire pour chaque fondation.',
            '*.carat_moyen.required'  => 'Le carat moyen est obligatoire pour chaque fondation.',
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

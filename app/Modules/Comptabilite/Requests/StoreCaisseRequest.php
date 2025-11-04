<?php

namespace App\Modules\Comptabilite\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreCaisseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_type_operation' => 'required|integer|exists:type_operations,id',
            'id_devise'         => 'required|integer|exists:devises,id',
            'montant'           => 'required|numeric|min:0',
            'id_compte'         => 'required|integer|exists:comptes,id', 
            'taux_jour'         => 'nullable|numeric|min:0',
            'commentaire'       => 'nullable|string|max:255',
            'reference'         => 'nullable|string|max:100',
            'date_operation'    => 'nullable|date',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'status'  => 'error',
            'message' => 'Erreur de validation.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}

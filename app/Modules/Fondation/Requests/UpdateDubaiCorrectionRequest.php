<?php

namespace App\Modules\Fondation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UpdateDubaiCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'corrections' => 'required|array|min:1',
            'corrections.*.id' => 'required|integer|exists:fondations,id',
            'corrections.*.poids_dubai' => 'required|numeric|min:0',
            'corrections.*.carrat_dubai' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'corrections.required' => 'La liste des corrections est obligatoire.',
            'corrections.*.id.exists' => 'Certaines fondations sont introuvables.',
            'corrections.*.poids_dubai.required' => 'Le poids de Dubaï est requis.',
            'corrections.*.carrat_dubai.required' => 'Le carat de Dubaï est requis.',
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

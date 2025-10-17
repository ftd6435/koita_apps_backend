<?php

namespace App\Modules\Purchase\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '*.id' => ['nullable', 'exists:barres,id'],
            '*.achat_id' => ['required', 'exists:achats,id'],
            '*.poid_pure' => ['required', 'numeric', 'min:0'],
            '*.carrat_pure' => ['required', 'numeric', 'min:0'],
            '*.densite' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            '*.id.exists' => 'Cette barre spécifié est invalide ou n’existe pas.',

            '*.achat_id.required' => 'Le champ achat_id est obligatoire.',
            '*.achat_id.exists' => 'L’achat spécifié est invalide ou n’existe pas.',

            '*.poid_pure.required' => 'Le champ poids pur est obligatoire.',
            '*.poid_pure.numeric' => 'Le poids pur doit être un nombre.',
            '*.poid_pure.min' => 'Le poids pur doit être supérieur ou égal à 0.',

            '*.carrat_pure.required' => 'Le champ carat pur est obligatoire.',
            '*.carrat_pure.numeric' => 'Le carat pur doit être un nombre.',
            '*.carrat_pure.min' => 'Le carat pur doit être supérieur ou égal à 0.',

            '*.densite.numeric' => 'La densité doit être un nombre.',
            '*.densite.min' => 'La densité doit être supérieure ou égale à 0.',
        ];
    }
}

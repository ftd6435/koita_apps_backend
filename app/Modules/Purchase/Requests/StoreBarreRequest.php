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
            // 🔹 The whole fixing object is optional (nullable)
            'fixing' => ['nullable', 'array'],
            'fixing.discount' => ['nullable', 'numeric', 'min:0'],
            'fixing.bourse' => ['nullable', 'numeric', 'min:0'],
            'fixing.unit_price' => ['nullable', 'numeric', 'min:0'],
            'fixing.devise_id' => ['nullable', 'exists:devises,id'],

            // 🔹 Barres array (required)
            'barres' => ['required', 'array', 'min:1'],
            'barres.*.id' => ['nullable', 'exists:barres,id'],
            'barres.*.achat_id' => ['required', 'exists:achats,id'],
            'barres.*.poid_pure' => ['required', 'numeric', 'min:0'],
            'barres.*.carrat_pure' => ['required', 'numeric', 'min:0'],
            'barres.*.densite' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            // 🔹 Fixing messages
            'fixing.array' => 'Les informations du fixing doivent être sous forme de tableau.',
            'fixing.discount.numeric' => 'La remise doit être un nombre.',
            'fixing.discount.min' => 'La remise doit être supérieure ou égale à 0.',
            'fixing.bourse.numeric' => 'La bourse doit être un nombre.',
            'fixing.bourse.min' => 'La bourse doit être supérieure ou égale à 0.',
            'fixing.unit_price.numeric' => 'Le prix unitaire doit être un nombre.',
            'fixing.unit_price.min' => 'Le prix unitaire doit être supérieur ou égal à 0.',
            'fixing.devise_id.exists' => 'La devise spécifiée est invalide ou n’existe pas.',

            // 🔹 Barres messages
            'barres.required' => 'La liste des barres est obligatoire.',
            'barres.array' => 'Les barres doivent être fournies sous forme de tableau.',

            'barres.*.achat_id.required' => 'Le champ achat_id est obligatoire.',
            'barres.*.achat_id.exists' => 'L’achat spécifié est invalide ou n’existe pas.',

            'barres.*.poid_pure.required' => 'Le champ poids pur est obligatoire.',
            'barres.*.poid_pure.numeric' => 'Le poids pur doit être un nombre.',
            'barres.*.poid_pure.min' => 'Le poids pur doit être supérieur ou égal à 0.',

            'barres.*.carrat_pure.required' => 'Le champ carat pur est obligatoire.',
            'barres.*.carrat_pure.numeric' => 'Le carat pur doit être un nombre.',
            'barres.*.carrat_pure.min' => 'Le carat pur doit être supérieur ou égal à 0.',

            'barres.*.densite.numeric' => 'La densité doit être un nombre.',
            'barres.*.densite.min' => 'La densité doit être supérieure ou égale à 0.',
        ];
    }
}

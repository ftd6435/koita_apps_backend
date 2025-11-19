<?php

namespace App\Modules\Fixing\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFixingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fournisseur_id' => ['required', 'integer', 'exists:fournisseurs,id'],
            'poids_pro' => ['nullable', 'numeric', 'min:0'],
            'carrat_moyenne' => ['nullable', 'numeric', 'min:0', 'max:25'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'bourse' => ['nullable', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'devise_id' => ['nullable', 'integer', 'exists:devises,id'],
            'barres' => ['nullable', 'array'],
            'barres.*.id' => ['required_with:barres', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'fournisseur_id.required' => 'Le fournisseur est obligatoire.',
            'fournisseur_id.integer' => 'Le fournisseur doit être un identifiant valide.',
            'fournisseur_id.exists' => 'Le fournisseur sélectionné est introuvable.',

            'poids_pro.numeric' => 'Le poids doit être un nombre.',
            'poids_pro.min' => 'Le poids ne peut pas être inférieur à 0.',

            'carrat_moyenne.numeric' => 'Le carat moyen doit être un nombre.',
            'carrat_moyenne.min' => 'Le carat moyen doit être au minimum de 0.',
            'carrat_moyenne.max' => 'Le carat moyen ne peut pas dépasser 25.',

            'discount.numeric' => 'La remise doit être un nombre.',
            'discount.min' => 'La remise ne peut pas être inférieure à 0.',

            'bourse.numeric' => 'La valeur de la bourse doit être un nombre.',
            'bourse.min' => 'La valeur de la bourse ne peut pas être inférieure à 0.',

            'unit_price.required' => 'Le prix unitaire est obligatoire.',
            'unit_price.numeric' => 'Le prix unitaire doit être un nombre.',
            'unit_price.min' => 'Le prix unitaire ne peut pas être inférieur à 0.',

            'devise_id.integer' => 'La devise doit être un identifiant valide.',
            'devise_id.exists' => 'La devise sélectionnée est introuvable.',

            'barres.array' => 'Les barres doivent être envoyées sous forme de tableau.',

            'barres.*.id.required_with' => 'L’identifiant de chaque barre est obligatoire lorsque des barres sont envoyées.',
            'barres.*.id.integer' => 'L’identifiant de chaque barre doit être un nombre entier.',
        ];
    }
}

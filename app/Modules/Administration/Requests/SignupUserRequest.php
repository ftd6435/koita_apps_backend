<?php

namespace App\Modules\Administration\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SignupUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3'],
            'telephone' => ['required', 'min:9', 'max:30', 'regex:/^[0-9]+$/', Rule::unique('users')->ignore($this->route()->parameter('user'))],
            'adresse' => ['nullable', 'string', 'min:3'],
            'image' => ['nullable', 'mimes:jpeg,jpg,png', 'max:1024'],
            'role' => ['nullable', 'in:super_admin,admin,user'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->route()->parameter('user'))],
            'password' => ['required', 'string', 'confirmed', 'min:6'],
        ];
    }

    public function messages()
    {
        return [
            'name' => 'Le nom dois composer au moins de trois caractères',
            'telephone' => 'Le numéro est obligatoire et dois avoir au minimum 9 digits ex: 600000000',
            'telephone.unique' => 'Ce numéro de telephone a été déjà utilisé',
            'adresse' => "L'adresse doit composer au moins de trois caractères",
            'email.required' => "L'adresse email est obligatoire ex: example@gmail.com",
            'email.unique' => 'Cette adresse email a été déjà utilisé',
            'role' => "Le role est soit super_admin, admin ou user",
            'image' => "L'image dois etre en jpg jpeg ou png et max: 1024",
            'password' => 'Le mot de passe est obligatoire',
            'password.confirmation' => 'Veuillez confirmer le mot de passe',
        ];
    }
}

<?php

namespace App\Modules\Administration\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
            'telephone' => ['nullable', 'min:9', 'max:30', 'regex:/^[0-9]+$/'],
            'adresse' => ['nullable', 'string', 'min:3'],
            'role' => ['nullable', 'in:super_admin,admin,user'],
            'image' => ['nullable', 'mimes:jpeg,jpg,png', 'max:1024'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->route()->parameter('id'))],
        ];
    }

    public function messages()
    {
        return [
            'name' => 'Le nom dois composer au moins de trois caractères',
            'telephone' => 'Le numéro doit avoir au minimum 9 digits ex: 600000000',
            'adresse' => "L'adresse doit composer au moins de trois caractères",
            'email.required' => "L'adresse email est obligatoire ex: example@gmail.com",
            'email.unique' => 'Cette adresse email a été déjà utilisé',
            'role' => "Le role est soit super_admin, admin ou user",
            'image' => "L'image dois etre en jpg jpeg ou png et max: 1024",
        ];
    }
}

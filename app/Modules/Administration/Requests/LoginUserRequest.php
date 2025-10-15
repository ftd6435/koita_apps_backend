<?php

namespace App\Modules\Administration\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginUserRequest extends FormRequest
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
            'telephone' => ['required', 'string'],
            'password' => ['required', 'min:6']
        ];
    }

    public function messages()
    {
        return [
            'telephone' => "Ce champ est obligatoire",
            'password' => "Ce champ est obligatoire avec 6 caractères au minimum"
        ];
    }
}

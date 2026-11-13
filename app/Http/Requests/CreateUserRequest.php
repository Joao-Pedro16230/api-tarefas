<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:100',
            ],
            'email' => [
                'required',
                'email',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:72',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O campo nome é obrigatório.',
            'name.string' => 'O campo nome precisa ser um texto.',
            'name.max' => 'O campo nome deve ter no máximo :max caracteres.',
            'name.min' => 'O campo nome deve ter no mínimo :min caracteres.',

            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'O campo email deve conter um endereço válido.',
            'email.unique' => 'Já existe um usuário cadastrado com esse email.',

            'password.required' => 'O campo senha é obrigatório.',
            'password.string' => 'O campo senha deve ser um texto.',
            'password.min' => 'A senha deve conter no mínimo :min caracteres.',
            'password.max' => 'A senha deve conter no máximo :max caracteres.',
        ];
    }
}

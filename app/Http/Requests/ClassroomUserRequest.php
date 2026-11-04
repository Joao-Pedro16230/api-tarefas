<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassroomUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'action' => 'required|in:add,remove',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'O usuário é obrigatório.',
            'user_id.integer' => 'O usuário deve ser um número inteiro.',
            'user_id.exists' => 'O usuário informado não existe.',
            'action.required' => 'A ação é obrigatória.',
            'action.in' => 'A ação deve ser add ou remove.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'title' => 'required|string|min:2|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'is_completed' => 'sometimes|boolean',

            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->where('user_id', $userId),
            ],

            'tags' => 'sometimes|array',
            'tags.*' => [
                'integer',
                'distinct',
                Rule::exists('tags', 'id')
                    ->where('user_id', $userId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título da tarefa é obrigatório.',
            'title.string' => 'O título deve ser um texto.',
            'title.min' => 'O título deve ter pelo menos 2 caracteres.',
            'title.max' => 'O título deve ter no máximo 255 caracteres.',

            'description.string' => 'A descrição deve ser um texto.',
            'due_date.date' => 'Informe uma data válida.',
            'is_completed.boolean' => 'O status da tarefa deve ser verdadeiro ou falso.',

            'category_id.integer' => 'A categoria deve ser um número inteiro.',
            'category_id.exists' => 'A categoria informada não existe ou não pertence ao usuário.',

            'tags.array' => 'As etiquetas devem ser enviadas em uma lista.',
            'tags.*.integer' => 'O identificador da etiqueta deve ser um número inteiro.',
            'tags.*.distinct' => 'Não é permitido repetir a mesma etiqueta.',
            'tags.*.exists' => 'Uma das etiquetas não existe ou não pertence ao usuário.',
        ];
    }
}

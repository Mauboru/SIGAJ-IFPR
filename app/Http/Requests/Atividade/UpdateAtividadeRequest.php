<?php

namespace App\Http\Requests\Atividade;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAtividadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isProfessor();
    }

    public function rules(): array
    {
        return [
            'titulo' => 'sometimes|required|string|max:255',
            'descricao' => 'nullable|string',
            'data_entrega' => 'sometimes|required|date',
            'valor_maximo' => 'sometimes|required|numeric|min:0|max:100',
            'turma_id' => 'sometimes|required|exists:turmas,id',
            'materia_id' => 'sometimes|required|exists:materias,id',
            'aula_id' => 'nullable|exists:aulas,id',
        ];
    }
}



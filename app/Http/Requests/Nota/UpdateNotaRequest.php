<?php

namespace App\Http\Requests\Nota;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isProfessor();
    }

    public function rules(): array
    {
        return [
            'aluno_id' => 'sometimes|required|exists:users,id',
            'turma_id' => 'sometimes|required|exists:turmas,id',
            'materia_id' => 'sometimes|required|exists:materias,id',
            'atividade_id' => 'nullable|exists:atividades,id',
            'prova_id' => 'nullable|exists:provas,id',
            'valor' => 'sometimes|required|numeric|min:0',
            'observacoes' => 'nullable|string',
        ];
    }
}



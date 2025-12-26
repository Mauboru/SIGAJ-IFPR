<?php

namespace App\Http\Requests\Nota;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isProfessor();
    }

    public function rules(): array
    {
        return [
            'aluno_id' => 'required|exists:users,id',
            'turma_id' => 'required|exists:turmas,id',
            'materia_id' => 'required|exists:materias,id',
            'atividade_id' => 'nullable|exists:atividades,id|required_without:prova_id',
            'prova_id' => 'nullable|exists:provas,id|required_without:atividade_id',
            'valor' => 'required|numeric|min:0',
            'observacoes' => 'nullable|string',
        ];
    }
}


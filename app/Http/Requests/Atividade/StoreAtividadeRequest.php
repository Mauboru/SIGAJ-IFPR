<?php

namespace App\Http\Requests\Atividade;

use Illuminate\Foundation\Http\FormRequest;

class StoreAtividadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isProfessor();
    }

    public function rules(): array
    {
        return [
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'data_entrega' => 'required|date',
            'valor_maximo' => 'required|numeric|min:0|max:100',
            'turma_id' => 'required|exists:turmas,id',
            'materia_id' => 'required|exists:materias,id',
            'aula_id' => 'nullable|exists:aulas,id',
        ];
    }
}





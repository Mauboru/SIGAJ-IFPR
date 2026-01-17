<?php

namespace App\Http\Requests\Aula;

use Illuminate\Foundation\Http\FormRequest;

class StoreAulaRequest extends FormRequest
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
            'data' => 'required|date',
            'turma_id' => 'required|exists:turmas,id',
            'materia_id' => 'required|exists:materias,id',
            'quantidade_aulas' => 'required|integer|min:1|max:10',
        ];
    }
}





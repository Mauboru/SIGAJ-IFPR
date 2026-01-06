<?php

namespace App\Http\Requests\Aula;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAulaRequest extends FormRequest
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
            'data' => 'sometimes|required|date',
            'turma_id' => 'sometimes|required|exists:turmas,id',
            'materia_id' => 'sometimes|required|exists:materias,id',
        ];
    }
}



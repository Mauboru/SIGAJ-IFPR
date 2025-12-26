<?php

namespace App\Http\Requests\Turma;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTurmaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isProfessor();
    }

    public function rules(): array
    {
        return [
            'nome' => 'sometimes|required|string|max:255',
            'materia_id' => 'sometimes|required|exists:materias,id',
            'ano' => 'sometimes|required|string|max:255',
            'semestre' => 'sometimes|required|string|max:255',
        ];
    }
}


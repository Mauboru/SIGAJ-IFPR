<?php

namespace App\Http\Requests\Turma;

use Illuminate\Foundation\Http\FormRequest;

class StoreTurmaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isProfessor();
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'materia_id' => 'required|exists:materias,id',
            'ano' => 'required|string|max:255',
            'semestre' => 'required|string|max:255',
        ];
    }
}



<?php

namespace App\Http\Requests\Prova;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProvaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isProfessor();
    }

    public function rules(): array
    {
        return [
            'titulo' => 'sometimes|required|string|max:255',
            'observacoes' => 'nullable|string',
            'data' => 'sometimes|required|date',
            'valor_total' => 'sometimes|required|numeric|min:0|max:100',
            'turma_id' => 'sometimes|required|exists:turmas,id',
            'materia_id' => 'sometimes|required|exists:materias,id',
        ];
    }
}





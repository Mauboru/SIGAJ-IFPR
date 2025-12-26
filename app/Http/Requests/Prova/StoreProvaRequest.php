<?php

namespace App\Http\Requests\Prova;

use Illuminate\Foundation\Http\FormRequest;

class StoreProvaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isProfessor();
    }

    public function rules(): array
    {
        return [
            'titulo' => 'required|string|max:255',
            'observacoes' => 'nullable|string',
            'data' => 'required|date',
            'valor_total' => 'required|numeric|min:0|max:100',
            'turma_id' => 'required|exists:turmas,id',
            'materia_id' => 'required|exists:materias,id',
        ];
    }
}


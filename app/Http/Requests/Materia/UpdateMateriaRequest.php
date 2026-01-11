<?php

namespace App\Http\Requests\Materia;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isProfessor();
    }

    public function rules(): array
    {
        return [
            'nome' => 'sometimes|required|string|max:255',
            'descricao' => 'nullable|string',
            'aulas' => 'nullable|array',
            'aulas.*.titulo' => 'required_with:aulas|string|max:255',
            'aulas.*.data' => 'required_with:aulas|date',
            'turma_ids' => 'nullable|array',
            'turma_ids.*' => 'exists:turmas,id',
            'arquivos_extras' => 'nullable|array',
            'arquivos_extras.*' => 'file|mimes:pdf|max:10240',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('aulas')) {
            $aulasValue = $this->input('aulas');
            if (is_string($aulasValue)) {
                $decoded = json_decode($aulasValue, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $this->merge(['aulas' => $decoded]);
                } else {
                    $this->merge(['aulas' => []]);
                }
            } elseif (!is_array($aulasValue)) {
                $this->merge(['aulas' => []]);
            }
        }
        
        if ($this->has('turma_ids')) {
            $turmaIds = $this->input('turma_ids');
            if (is_array($turmaIds)) {
                $this->merge(['turma_ids' => array_values(array_filter($turmaIds))]);
            }
        }
    }
}


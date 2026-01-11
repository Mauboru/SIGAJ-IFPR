<?php

namespace App\Http\Requests\Materia;

use Illuminate\Foundation\Http\FormRequest;

class StoreMateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isProfessor();
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'turma_ids' => 'nullable|array',
            'turma_ids.*' => 'exists:turmas,id',
            'aulas' => 'nullable|array',
            'aulas.*.titulo' => 'required_with:aulas|string|max:255',
            'aulas.*.data' => 'required_with:aulas|date',
            'arquivos_extras' => 'nullable|array',
            'arquivos_extras.*' => 'file|mimes:pdf|max:10240',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Se aulas vier como string JSON, decodificar para array
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
                // Se não for string nem array, converter para array vazio
                $this->merge(['aulas' => []]);
            }
        }
        
        // Garantir que turma_ids seja array se vier como array indexado
        if ($this->has('turma_ids')) {
            $turmaIds = $this->input('turma_ids');
            if (is_array($turmaIds)) {
                // Se for array indexado como [0 => 1, 1 => 2], converter para [1, 2]
                $this->merge(['turma_ids' => array_values(array_filter($turmaIds))]);
            }
        }
    }
}


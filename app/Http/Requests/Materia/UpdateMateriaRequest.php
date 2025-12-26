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
            'plano_ensino' => 'nullable|file|mimes:pdf|max:10240',
        ];
    }
}


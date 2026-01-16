<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:professor,aluno',
            'instituicao' => 'nullable|string|max:255',
            'foto' => 'nullable|image|max:2048',
            'turma_ids' => 'nullable|array',
            'turma_ids.*' => 'exists:turmas,id',
        ];
    }
}





<?php

namespace App\Policies;

use App\Models\Aula;
use App\Models\User;

class AulaPolicy
{
    public function view(User $user, Aula $aula): bool
    {
        // Professor vê aulas de suas turmas
        if ($user->isProfessor()) {
            return $aula->turma->professor_id === $user->id;
        }

        // Aluno vê aulas de turmas que está matriculado
        return $aula->turma->alunos()->where('aluno_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        // Apenas professores podem criar aulas
        return $user->isProfessor();
    }

    public function update(User $user, Aula $aula): bool
    {
        // Apenas o professor dono da turma pode atualizar
        return $user->isProfessor() && $aula->turma->professor_id === $user->id;
    }

    public function delete(User $user, Aula $aula): bool
    {
        // Apenas o professor dono da turma pode deletar
        return $user->isProfessor() && $aula->turma->professor_id === $user->id;
    }
}





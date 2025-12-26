<?php

namespace App\Policies;

use App\Models\Turma;
use App\Models\User;

class TurmaPolicy
{
    public function view(User $user, Turma $turma): bool
    {
        // Professor vê suas turmas
        if ($user->isProfessor()) {
            return $turma->professor_id === $user->id;
        }

        // Aluno vê turmas que está matriculado
        return $turma->alunos()->where('aluno_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        // Apenas professores podem criar turmas
        return $user->isProfessor();
    }

    public function update(User $user, Turma $turma): bool
    {
        // Apenas o professor dono pode atualizar
        return $user->isProfessor() && $turma->professor_id === $user->id;
    }

    public function delete(User $user, Turma $turma): bool
    {
        // Apenas o professor dono pode deletar
        return $user->isProfessor() && $turma->professor_id === $user->id;
    }
}


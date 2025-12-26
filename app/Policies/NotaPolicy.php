<?php

namespace App\Policies;

use App\Models\Nota;
use App\Models\User;

class NotaPolicy
{
    public function view(User $user, Nota $nota): bool
    {
        // Professor vê notas de suas turmas
        if ($user->isProfessor()) {
            return $nota->turma->professor_id === $user->id;
        }

        // Aluno vê apenas suas próprias notas
        return $nota->aluno_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Apenas professores podem criar notas
        return $user->isProfessor();
    }

    public function update(User $user, Nota $nota): bool
    {
        // Apenas o professor dono da turma pode atualizar
        return $user->isProfessor() && $nota->turma->professor_id === $user->id;
    }

    public function delete(User $user, Nota $nota): bool
    {
        // Apenas o professor dono da turma pode deletar
        return $user->isProfessor() && $nota->turma->professor_id === $user->id;
    }
}


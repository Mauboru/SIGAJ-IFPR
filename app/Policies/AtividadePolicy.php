<?php

namespace App\Policies;

use App\Models\Atividade;
use App\Models\User;

class AtividadePolicy
{
    public function view(User $user, Atividade $atividade): bool
    {
        // Professor vê atividades de suas turmas
        if ($user->isProfessor()) {
            return $atividade->turma->professor_id === $user->id;
        }

        // Aluno vê atividades de turmas que está matriculado
        return $atividade->turma->alunos()->where('aluno_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        // Apenas professores podem criar atividades
        return $user->isProfessor();
    }

    public function update(User $user, Atividade $atividade): bool
    {
        // Apenas o professor dono da turma pode atualizar
        return $user->isProfessor() && $atividade->turma->professor_id === $user->id;
    }

    public function delete(User $user, Atividade $atividade): bool
    {
        // Apenas o professor dono da turma pode deletar
        return $user->isProfessor() && $atividade->turma->professor_id === $user->id;
    }
}





<?php

namespace App\Policies;

use App\Models\Prova;
use App\Models\User;

class ProvaPolicy
{
    public function view(User $user, Prova $prova): bool
    {
        // Professor vê provas de suas turmas
        if ($user->isProfessor()) {
            return $prova->turma->professor_id === $user->id;
        }

        // Aluno vê provas de turmas que está matriculado
        return $prova->turma->alunos()->where('aluno_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        // Apenas professores podem criar provas
        return $user->isProfessor();
    }

    public function update(User $user, Prova $prova): bool
    {
        // Apenas o professor dono da turma pode atualizar
        return $user->isProfessor() && $prova->turma->professor_id === $user->id;
    }

    public function delete(User $user, Prova $prova): bool
    {
        // Apenas o professor dono da turma pode deletar
        return $user->isProfessor() && $prova->turma->professor_id === $user->id;
    }
}


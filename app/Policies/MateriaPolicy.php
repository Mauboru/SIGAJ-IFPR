<?php

namespace App\Policies;

use App\Models\Materia;
use App\Models\User;

class MateriaPolicy
{
    public function view(User $user, Materia $materia): bool
    {
        // Professor vê suas matérias
        if ($user->isProfessor()) {
            return $materia->professor_id === $user->id;
        }

        // Aluno vê matérias das turmas que está matriculado
        return $user->turmasComoAluno()
            ->where('materia_id', $materia->id)
            ->exists();
    }

    public function create(User $user): bool
    {
        // Apenas professores podem criar matérias
        return $user->isProfessor();
    }

    public function update(User $user, Materia $materia): bool
    {
        // Apenas o professor dono pode atualizar
        return $user->isProfessor() && $materia->professor_id === $user->id;
    }

    public function delete(User $user, Materia $materia): bool
    {
        // Apenas o professor dono pode deletar
        return $user->isProfessor() && $materia->professor_id === $user->id;
    }
}



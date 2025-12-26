<?php

namespace App\Policies;

use App\Models\User;

class UsuarioPolicy
{
    public function view(User $user, User $model): bool
    {
        // Professor pode ver seus dados e de alunos
        if ($user->isProfessor()) {
            return $user->id === $model->id || $model->isAluno();
        }

        // Aluno pode ver apenas seus dados
        return $user->id === $model->id;
    }

    public function update(User $user, User $model): bool
    {
        // Cada usuário pode editar apenas seus próprios dados
        return $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        // Apenas o próprio usuário pode deletar sua conta
        return $user->id === $model->id;
    }
}


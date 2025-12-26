<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'instituicao',
        'foto',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relacionamentos
    public function materias()
    {
        return $this->hasMany(Materia::class, 'professor_id');
    }

    public function turmasComoProfessor()
    {
        return $this->hasMany(Turma::class, 'professor_id');
    }

    public function turmasComoAluno()
    {
        return $this->belongsToMany(Turma::class, 'turma_aluno', 'aluno_id', 'turma_id');
    }

    public function notas()
    {
        return $this->hasMany(Nota::class, 'aluno_id');
    }

    // Métodos auxiliares
    public function isProfessor()
    {
        return $this->role === 'professor';
    }

    public function isAluno()
    {
        return $this->role === 'aluno';
    }
}

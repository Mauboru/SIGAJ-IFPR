<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turma extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'professor_id',
        'materia_id',
        'ano',
        'semestre',
    ];

    // Relacionamentos
    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'materia_turma');
    }

    // Manter compatibilidade com código existente - retorna a primeira matéria
    // Não deve ser usado em eager loading, use 'materias' ao invés
    public function getMateriaAttribute()
    {
        return $this->materias()->first();
    }

    public function alunos()
    {
        return $this->belongsToMany(User::class, 'turma_aluno', 'turma_id', 'aluno_id');
    }

    public function aulas()
    {
        return $this->hasMany(Aula::class);
    }

    public function atividades()
    {
        return $this->hasMany(Atividade::class);
    }

    public function provas()
    {
        return $this->hasMany(Prova::class);
    }

    public function notas()
    {
        return $this->hasMany(Nota::class);
    }
}

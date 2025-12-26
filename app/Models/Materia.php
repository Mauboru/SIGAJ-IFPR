<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Materia extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
        'professor_id',
        'plano_ensino_path',
    ];

    // Relacionamentos
    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    public function turmas()
    {
        return $this->hasMany(Turma::class);
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

    public function arquivos()
    {
        return $this->morphMany(Arquivo::class, 'arquivoable');
    }
}

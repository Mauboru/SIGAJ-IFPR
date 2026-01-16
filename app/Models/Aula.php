<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Aula extends Model
{
    use HasFactory;

    protected $fillable = [
        'turma_id',
        'materia_id',
        'titulo',
        'descricao',
        'data',
    ];

    protected $casts = [
        'data' => 'date',
    ];

    // Relacionamentos
    public function turma()
    {
        return $this->belongsTo(Turma::class);
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function atividades()
    {
        return $this->hasMany(Atividade::class);
    }

    public function arquivos()
    {
        return $this->morphMany(Arquivo::class, 'arquivoable');
    }

    public function presencas()
    {
        return $this->hasMany(Presenca::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    use HasFactory;

    protected $fillable = [
        'aluno_id',
        'turma_id',
        'materia_id',
        'atividade_id',
        'prova_id',
        'valor',
        'observacoes',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
    ];

    // Relacionamentos
    public function aluno()
    {
        return $this->belongsTo(User::class, 'aluno_id');
    }

    public function turma()
    {
        return $this->belongsTo(Turma::class);
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function atividade()
    {
        return $this->belongsTo(Atividade::class);
    }

    public function prova()
    {
        return $this->belongsTo(Prova::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Prova extends Model
{
    use HasFactory;

    protected $fillable = [
        'turma_id',
        'materia_id',
        'titulo',
        'data',
        'valor_total',
        'observacoes',
    ];

    protected $casts = [
        'data' => 'date',
        'valor_total' => 'decimal:2',
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

    public function notas()
    {
        return $this->hasMany(Nota::class);
    }

    public function arquivos()
    {
        return $this->morphMany(Arquivo::class, 'arquivoable');
    }
}

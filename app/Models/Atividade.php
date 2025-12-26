<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Atividade extends Model
{
    use HasFactory;

    protected $fillable = [
        'turma_id',
        'materia_id',
        'aula_id',
        'titulo',
        'descricao',
        'data_entrega',
        'valor_maximo',
    ];

    protected $casts = [
        'data_entrega' => 'date',
        'valor_maximo' => 'decimal:2',
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

    public function aula()
    {
        return $this->belongsTo(Aula::class);
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

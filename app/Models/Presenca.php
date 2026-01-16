<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presenca extends Model
{
    use HasFactory;

    protected $fillable = [
        'aula_id',
        'aluno_id',
        'presente',
        'observacoes',
    ];

    protected $casts = [
        'presente' => 'boolean',
    ];

    // Relacionamentos
    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }

    public function aluno()
    {
        return $this->belongsTo(User::class, 'aluno_id');
    }
}

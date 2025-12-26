<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arquivo extends Model
{
    use HasFactory;

    protected $fillable = [
        'arquivoable_type',
        'arquivoable_id',
        'nome_original',
        'caminho',
        'tipo',
        'mime_type',
        'tamanho',
    ];

    // Relacionamento polimórfico
    public function arquivoable()
    {
        return $this->morphTo();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projeto extends Model
{
    use HasFactory;

    protected $table = 'projetos';
    protected $primaryKey = 'CODIGO';
    public $timestamps = false;

    protected $fillable = [
        'COD_FUNCIONARIO_RESP',
        'NOME_PROJETO',
        'COD_CLIENTE',
        'CONTATO_EMPRESA',
        'COD_PROGRAMADOR_CHEFE',
        'OBSERVACOES',
        'COD_STATUS_SOFTWARE',
        'NOTIF_CLIENTE',
        'NOTIF_PROGRAMADOR',
        'DATA_INICIAL',
        'DATA_FINAL',
        'NUM_DEPENDENCIAS',
        'COD_ANALISTA_RESP',
    ];
}


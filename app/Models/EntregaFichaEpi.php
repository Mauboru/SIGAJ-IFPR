<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntregaFichaEpi extends Model {
    protected $table = 'prod_entrega_ficha_epi';
    protected $primaryKey = 'CODIGO';
    public $timestamps = false;

    protected $fillable = [
        'COD_FICHA_ENTREGA',
        'COD_ENTREGA_EPI',
        'COD_PROD_ENTREGA_EPI',
        'QUANTIDADE',
        'DESCRICAO_EPI',
        'COD_EPI',
        'COD_PROJETO',
        'VALOR',
        'SUBTOTAL',
        'DATA_ENTREGA',
        'DATA_VENCIMENTO',
    ];
}
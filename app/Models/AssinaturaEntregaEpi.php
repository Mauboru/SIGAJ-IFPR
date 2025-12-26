<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\AssinaturaEntregaEpi;
use Illuminate\Database\Eloquent\Model;

class AssinaturaEntregaEpi extends Model {
    protected $table = 'assinatura_prod_entrega_epi';
    protected $primaryKey = 'CODIGO';
    public $incrementing = true;
    protected $keyType = 'int'; 
    public $timestamps = false;

    protected $hidden = ['ASSINATURA_BLOB'];

    protected $fillable = [
        'COD_ENTREGA_PROD_EPI',
        'COD_ASSINATURA_FICHA',
        'COD_BAIXA_PRODUTO',
        'ASSINATURA',
        'ASSINATURA_HEX',
        'ASSINATURA_BLOB',
        'DATA_DEVOLUCAO'
    ];
}
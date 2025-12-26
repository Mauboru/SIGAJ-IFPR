<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssinaturaFichaEpi extends Model {
    protected $table = 'assinatura_ficha_epi';
    protected $primaryKey = 'CODIGO';
    public $incrementing = true;
    protected $keyType = 'int'; 
    public $timestamps = false;

    protected $hidden = ['ASSINATURA_BLOB'];

    protected $fillable = [
        'COD_FICHA_EPI',
        'ASSINATURA',
        'ASSINATURA_HEX',
        'ASSINATURA_BLOB',
    ];
}

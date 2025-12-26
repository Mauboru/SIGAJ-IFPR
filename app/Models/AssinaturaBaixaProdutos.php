<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\AssinaturaEntregaEpi;
use Illuminate\Database\Eloquent\Model;

class AssinaturaBaixaProdutos extends Model {
    protected $table = 'assinatura_baixa_produtos';
    protected $primaryKey = 'CODIGO';
    public $incrementing = true;
    protected $keyType = 'int'; 
    public $timestamps = false;

    protected $hidden = ['ASSINATURA_BLOB'];

    //protected $fillable = ['DATA_DEVOLUCAO'];
}
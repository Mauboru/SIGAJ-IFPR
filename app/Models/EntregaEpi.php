<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntregaEpi extends Model {
    protected $table = 'prod_entrega_epi';
    protected $primaryKey = 'CODIGO';
    public $timestamps = false;

    public function assinaturas() {
        return $this->hasMany(AssinaturaEntregaEpi::class, 'COD_ENTREGA_PROD_EPI', 'CODIGO');
    }
}
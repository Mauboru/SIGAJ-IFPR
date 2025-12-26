<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaixaProdutos extends Model
{
    use HasFactory;

    protected $table = 'baixa_produtos';
    protected $primaryKey = 'CODIGO';
    public $timestamps = false;

    protected $fillable = [
        'DATA_CRIACAO',
        'DATA_ALTERACAO',
        'COD_FUNCIONARIO_RESP',
        'DATA_ENTREGA',
        'NUM_ITENS',
        'OBS',
        'COD_FUNCIONAR_RECEBE',
        'COD_DESTINO',
        'COD_TIPO_BAIXA_ESTOQUE',
        'NUM_PROJETO',
        'TOTAL_GERAL',
        'COD_CENTRO_CUSTO',
        'CLIENTE_FORNECEDOR',
        'DATA_EMISSAO',
        'COD_FUNCIONARIO_ENTREGA',
        'COD_PROJETO',
        'COD_ENTREGA_UNIFORME',
        'NUM_BAIXA',
    ];

    public function funcionarioRecebe()
    {
        return $this->belongsTo(Funcionario::class, 'COD_FUNCIONAR_RECEBE', 'CODIGO');
    }

    public function funcionarioEntrega()
    {
        return $this->belongsTo(Funcionario::class, 'COD_FUNCIONARIO_ENTREGA', 'CODIGO');
    }

    public function centroCusto()
    {
        return $this->belongsTo(CentroCusto::class, 'COD_CENTRO_CUSTO', 'CODIGO');
    }

    public function assinatura()
    {
        return $this->hasOne(AssinaturaBaixaProdutos::class, 'COD_BAIXA_PRODUTOS', 'CODIGO');
    }
}

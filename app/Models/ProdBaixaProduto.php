<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdBaixaProduto extends Model
{
    use HasFactory;

    protected $table = 'prod_baixa_produtos';
    protected $primaryKey = 'CODIGO';
    public $timestamps = false;

    protected $fillable = [
        'COD_BAIXA_PRODUTO',
        'COD_PRODUTO',
        'DESCRICAO_PRODUTO',
        'QUANTIDADE',
        'COD_TIPO_ESTOQUE',
        'INTEGRADO_PROJETO',
        'NUM_PROJETO',
        'PRECO',
        'SUBTOTAL',
        'DATA_ENTREGA',
        'COD_UNIDADE',
        'VINCULO_PROJETO',
        'COD_CATEGORIA',
        'QTD_DEVOLVIDO',
        'COD_STATUS',
        'COD_PROJETO',
        'NUM_CA',
        'DATA_DEVOLUCAO',
    ];

    public function assinatura()
    {
        return $this->hasOne(AssinaturaBaixaProdutos::class, 'COD_BAIXA_PRODUTOS', 'COD_BAIXA_PRODUTO');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'COD_PRODUTO', 'CODIGO');
    }

    public function baixa()
    {
        return $this->belongsTo(BaixaProdutos::class, 'COD_BAIXA_PRODUTO', 'CODIGO');
    }

    public function projeto()
    {
        return $this->belongsTo(Projeto::class, 'COD_PROJETO', 'CODIGO');
    }
}

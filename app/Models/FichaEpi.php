<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichaEpi extends Model {
    protected $table = 'ficha_epi';
    protected $primaryKey = 'CODIGO';
    public $timestamps = false;

    protected $fillable = [
        'DATA_CADASTRO',
        'DATA_ADMISSAO',
        'DATA_EMISSAO',
        'DATA_ALTERACAO',
        'DATA_INICIAL_ENTREGA',
        'DATA_FINAL_ENTREGA',
        'COD_FUNCIONARIO_RESP',
        'COD_FUNCIONARIO',
        'COD_FUNCAO',
        'COD_SETOR',
        'MATRICULA',
        'OBS',
    ];
    
    public function funcao() {
        return $this->belongsTo(Funcoes::class, 'COD_FUNCAO', 'CODIGO');
    }

    public function departamento() {
        return $this->belongsTo(Departamentos::class, 'COD_SETOR', 'CODIGO');
    }

    public function funcionario() {
        return $this->belongsTo(Funcionario::class, 'COD_FUNCIONARIO', 'CODIGO');
    }
}
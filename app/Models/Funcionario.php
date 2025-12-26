<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model {
    protected $table = 'funcionarios';
    protected $primaryKey = 'CODIGO';

    public function funcao() {
        return $this->belongsTo(Funcoes::class, 'COD_FUNCAO', 'CODIGO');
    }

    public function departamento() {
        return $this->belongsTo(Departamentos::class, 'COD_DEPARTAMENTO', 'CODIGO');
    }
}
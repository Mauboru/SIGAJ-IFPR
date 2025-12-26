<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable {
    use HasApiTokens, Notifiable, HasFactory;

    protected $table = 'usuarios';
    protected $primaryKey = 'CODIGO';

    protected $fillable = [
        'COD_FUNCIONARIO',
        'NOME',
        'SENHA',
        'TIPO',
    ];

    protected $hidden = [
        'SENHA',
    ];

    protected $casts = [
        'COD_FUNCIONARIO' => 'integer',
        'NOME' => 'string',
        'SENHA' => 'string',
        'TIPO' => 'string',
    ];

    // public function funcionario() {
    //     return $this->belongsTo(Funcionario::class, 'COD_FUNCIONARIO');
    // }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroCusto extends Model
{
    use HasFactory;

    protected $table = 'centro_custo';
    protected $primaryKey = 'CODIGO';
    public $timestamps = false;

    protected $fillable = [
        'CENTRO_CUSTO',
    ];
}


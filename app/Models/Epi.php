<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Epi extends Model {
    protected $table = 'epi';
    protected $primaryKey = 'CODIGO';
    public $timestamps = false;
}
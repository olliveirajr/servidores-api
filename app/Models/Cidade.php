<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{
    class Cidade extends Model
    {
        use HasFactory;
        protected $table = 'cidades';
        protected $fillable = ['nome', 'uf'];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Endereco extends Model
{
    use HasFactory;
    protected $table = 'enderecos';
    protected $fillable = ['tipo_logradouro', 'logradouro', 'numero', 'bairro', 'cidade_id'];
}

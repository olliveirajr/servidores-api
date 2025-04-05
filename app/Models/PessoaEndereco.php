<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PessoaEndereco extends Model
{
    protected $table = 'pessoa_endereco';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'pessoa_id',
        'endereco_id',
    ];
}

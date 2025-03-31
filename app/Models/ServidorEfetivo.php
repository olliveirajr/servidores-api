<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServidorEfetivo extends Model
{
    protected $table = 'servidores_efetivos';

    protected $fillable = ['pessoa_id', 'matricula'];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class);
    }
}

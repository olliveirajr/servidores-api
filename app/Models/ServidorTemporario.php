<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServidorTemporario extends Model
{
    protected $table = 'servidores_temporarios';
    public $timestamps = false;

    protected $fillable = ['id', 'data_admissao', 'data_demissao'];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'id');
    }
}

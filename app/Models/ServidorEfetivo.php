<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServidorEfetivo extends Model
{
    protected $table = 'servidores_efetivos';
    public $incrementing = false;
    protected $primaryKey = 'pessoa_id';
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'matricula',
        'pessoa_id',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class);
    }

    public function fotos()
    {
        return $this->hasMany(FotosPessoa::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FotosPessoa;

class Pessoa extends Model
{
    protected $table = 'pessoas';

    protected $fillable = [
        'nome', 'data_nascimento', 'sexo', 'mae', 'pai'
    ];

    // Relacionamentos
    public function fotos()
    {
        return $this->hasMany(FotosPessoa::class);
    }

    public function servidorTemporario()
    {
        return $this->hasOne(ServidorTemporario::class);
    }

    public function servidorEfetivo()
    {
        return $this->hasOne(ServidorEfetivo::class, 'pessoa_id', 'id');
    }

    public function lotacoes()
    {
        return $this->hasMany(Lotacao::class);
    }

    public function enderecos()
    {
        return $this->belongsToMany(Endereco::class, 'pessoa_endereco');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lotacao extends Model
{
    protected $table = 'lotacoes';

    protected $fillable = [
        'pessoa_id', 'unidade_id', 'data_lotacao', 'data_remocao', 'portaria'
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class);
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class);
    }
}

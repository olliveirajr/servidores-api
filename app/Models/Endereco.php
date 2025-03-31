<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Endereco extends Model
{
    protected $table = 'enderecos';

    protected $fillable = [
        'tipo_logradouro', 'logradouro', 'numero', 'bairro', 'cidade_id'
    ];

    public function cidade()
    {
        return $this->belongsTo(Cidade::class);
    }

    public function pessoas()
    {
        return $this->belongsToMany(Pessoa::class, 'pessoa_endereco');
    }

    public function unidades()
    {
        return $this->belongsToMany(Unidade::class, 'unidade_endereco');
    }
}

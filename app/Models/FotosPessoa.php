<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotosPessoa extends Model
{
    protected $table = 'fotos_pessoas';

    protected $fillable = ['pessoa_id', 'data', 'bucket', 'hash'];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class);
    }
}

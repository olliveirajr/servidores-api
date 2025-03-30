<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotoPessoa extends Model
{
    use HasFactory;
    protected $table = 'fotos_pessoas';
    protected $fillable = ['pessoa_id', 'data', 'bucket', 'hash'];
}

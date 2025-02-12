<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TelefonePesquisa extends Model
{
    use SoftDeletes;

    protected $table = 'telefone_pesquisas';

    protected $fillable = [
        'whats'
    ];
}

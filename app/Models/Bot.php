<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{
    protected $table = 'bots';

    protected $fillable = [
        'nome',
        'contexto',
        'prompt',
        'temperature',
        'top_p',
        'model',
        'stream',
        'max_length',
    ];

    protected $casts = [
        'temperatura' => 'float',
        'top_p' => 'float', // Pode ser 'decimal:1' se quiser manter a precisão específica
        'stream' => 'boolean',
        'max_length' => 'integer',
    ];
}

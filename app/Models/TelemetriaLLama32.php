<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelemetriaLLama32 extends Model
{
    protected $fillable =
    [
        'embeddings',
        'temperature',
        'topP',
        'processamentoEmbeddings',
        'processamentoLLM',
        'respostaLLM',
    ];
}

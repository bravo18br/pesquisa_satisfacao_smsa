<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelemetriaLLama31 extends Model
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

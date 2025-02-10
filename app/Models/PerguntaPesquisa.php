<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerguntaPesquisa extends Model
{
    use HasFactory;

    protected $table = 'pergunta_pesquisas'; // Opcional, pois o Laravel já inferiria isso corretamente

    protected $fillable = [
        'nome',
        'mensagem',
    ];
}

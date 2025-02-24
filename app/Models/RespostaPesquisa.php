<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RespostaPesquisa extends Model
{
    use HasFactory;

    protected $table = 'resposta_pesquisas'; // Opcional, pois o Laravel já inferiria isso corretamente

    protected $fillable = [
        'numeroWhats',
        'autorizacaoLGPD',
        'nomeUnidadeSaude',
        'recepcaoUnidade',
        'limpezaUnidade',
        'exameQualidade',
        'medicoQualidade',
        'pontualidadeAtendimento',
        'observacaoLivre',
        'pesquisaConcluida',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessadaPesquisa extends Model
{
    use HasFactory;

    protected $table = 'processada_pesquisas';

    protected $fillable = [
        'numeroWhats',
        'primeiroContato',
        'autorizacaoLGPD',
        'nomeUnidadeSaude',
        'recepcaoUnidade',
        'limpezaUnidade',
        'medicoQualidade',
        'exameQualidade',
        'tempoAtendimento',
        'comentarioLivre',
        'pesquisaConcluida'
    ];
}

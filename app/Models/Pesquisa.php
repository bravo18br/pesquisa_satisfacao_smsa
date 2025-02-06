<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pesquisa extends Model
{
    use SoftDeletes;

    protected $table = 'pesquisas';

    protected $fillable = [
        'telefone',
        'unidade',
        'status_pesquisa',
        'atendimento_recepcao',
        'realizacao_exame',
        'atendimento_medico',
        'ambiente_limpeza',
        'pontualidade_exame',
        'avaliacao_geral',
        'avaliacao_livre',
    ];
}

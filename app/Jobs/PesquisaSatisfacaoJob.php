<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Pesquisa;
use App\Models\PerguntaPesquisa;
use App\Http\Controllers\EvolutionController;


class EnviarPesquisaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $numeroWhats;

    public function __construct($numeroWhats)
    {
        $this->numeroWhats = $numeroWhats;
    }

    public function handle()
    {
        if (!$this->numeroWhats) {
            return;
        }

        // return $this->release(60); // Reagenda a job para rodar novamente em 60 segundos

        $respostas = [
            'autorizacaoLGPD' => null,
            'nomeUnidadeSaude' => null,
            'recepcaoUnidade' => null,
            'limpezaUnidade' => null,
            'medicoQualidade' => null,
            'exameQualidade' => null,
            'tempoAtendimento' => null,
            'comentarioLivre' => null,
        ];

        while ($respostas['autorizacaoLGPD'] === null) {

            // Pesquisar nos eventos se tem alguma resposta do numeroWhats e processar

        $respostas['autorizacaoLGPD'] = 'não';
        }
    } 
}

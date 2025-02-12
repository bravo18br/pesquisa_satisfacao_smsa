<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Http\Controllers\EvolutionController;
use App\Http\Controllers\BotsController;

use App\Models\PerguntaPesquisa;
use App\Models\ProcessadaPesquisa;
use App\Models\EvolutionEvent;

class PesquisaSatisfacaoJob implements ShouldQueue
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

        // Recupera as respostas já registradas para este número
        $pesquisa = ProcessadaPesquisa::where('numeroWhats', $this->numeroWhats)->first();

        if (!$pesquisa) {
            return;
        }

        $respostas = [
            'numeroWhats' => $pesquisa->numeroWhats,
            'autorizacaoLGPD' => $pesquisa->autorizacaoLGPD,
            'nomeUnidadeSaude' => $pesquisa->nomeUnidadeSaude,
            'recepcaoUnidade' => $pesquisa->recepcaoUnidade,
            'limpezaUnidade' => $pesquisa->limpezaUnidade,
            'medicoQualidade' => $pesquisa->medicoQualidade,
            'exameQualidade' => $pesquisa->exameQualidade,
            'tempoAtendimento' => $pesquisa->tempoAtendimento,
            'comentarioLivre' => $pesquisa->comentarioLivre,
        ];

        while ($respostas['autorizacaoLGPD'] === null) {
            $mensagensAlvo = EvolutionEvent::where('fromMe', false)
                ->where('remoteJid', $this->numeroWhats)
                ->pluck('conversation')
                ->filter()
                ->implode(' ');
            if ($mensagensAlvo) {
                $bot = new BotsController();
                $pesquisa->autorizacaoLGPD = $bot->promptBot($mensagensAlvo, 'lgpdAutorizacaoBOT');

                if ($pesquisa->autorizacaoLGPD != 'sim') {
                    // significa que o usuário não autorizou a pesquisa. Nesse caso agradecer e encerrar o contato.
                    $agradecimento = PerguntaPesquisa::where('pesquisa', 'smsa')
                        ->where('nome', 'lgpdNegado')
                        ->first();
                    $agradecimento = $agradecimento['mensagem'];
                    $evolution = new EvolutionController();
                    $evolution->enviaWhats($this->numeroWhats, $agradecimento);
                }
                $pesquisa->save();
            } else {
                return $this->release(60);
            }
        }
    }
}

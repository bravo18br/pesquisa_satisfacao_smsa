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
    protected $tentativasMaximas = 10; // 🔹 Evita loop infinito

    public function __construct($numeroWhats)
    {
        $this->numeroWhats = $numeroWhats;
    }

    public function handle()
    {
        if (!$this->numeroWhats) {
            return;
        }

        // 🔹 Recupera pesquisa associada ao número
        $pesquisa = ProcessadaPesquisa::where('numeroWhats', $this->numeroWhats)->first();

        if (!$pesquisa) {
            return;
        }

        // 🔹 Verifica se já atingiu o limite de tentativas
        if ($this->attempts() > $this->tentativasMaximas) {
            // 🔹 Se o usuário não responder por um período de tempo, a pesquisa é encerrada
            $encerramento = PerguntaPesquisa::where('pesquisa', 'smsa')
                ->where('nome', 'semInteracao')
                ->first();

            if ($encerramento) {
                $evolution = new EvolutionController();
                $evolution->enviaWhats($this->numeroWhats, $encerramento->mensagem);
            }
            $this->fail("Número {$this->numeroWhats} atingiu o limite de tentativas."); // Marca como falha e para o job
            $pesquisa->autorizacaoLGPD = 'não';
            $pesquisa->numeroWhats = null;
            $pesquisa->save();
            return;
        }

        while ($pesquisa->autorizacaoLGPD === null) {
            $mensagensAlvo = EvolutionEvent::where('fromMe', false)
                ->where('remoteJid', $this->numeroWhats)
                ->pluck('conversation')
                ->filter()
                ->implode(' ');

            if ($mensagensAlvo) {
                $bot = new BotsController();
                $pesquisa->autorizacaoLGPD = $bot->promptBot($mensagensAlvo, 'lgpdAutorizacaoBOT');

                if ($pesquisa->autorizacaoLGPD != 'sim') {
                    // 🔹 Se o usuário não autorizou a pesquisa, envia agradecimento e remove o telefone
                    $agradecimento = PerguntaPesquisa::where('pesquisa', 'smsa')
                        ->where('nome', 'lgpdNegado')
                        ->first();

                    if ($agradecimento) {
                        $evolution = new EvolutionController();
                        $evolution->enviaWhats($this->numeroWhats, $agradecimento->mensagem);
                    }
                    $pesquisa->autorizacaoLGPD = 'não';
                    $pesquisa->numeroWhats = null; // 🔹 Remove o telefone para não tentar enviar novamente
                }

                $pesquisa->save();
            } else {
                return $this->release(120); // 🔹 Reagenda se não recebeu resposta ainda
            }
        }
    }
}

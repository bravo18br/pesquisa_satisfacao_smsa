<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\EvolutionController;
use App\Http\Controllers\BotsController;

use App\Models\ProcessadaPesquisa;
use App\Models\PerguntaPesquisa;

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
        Log::info("Iniciando JOB para: {$this->numeroWhats}");
        $evolution = new EvolutionController();
        $bot = new BotsController();

        $pesquisa = ProcessadaPesquisa::where('numeroWhats', $this->numeroWhats)
            ->where('pesquisaConcluida', false)
            ->first();

        if (!$pesquisa) {
            Log::warning("Nenhuma pesquisa encontrada para {$this->numeroWhats}");
            return;
        }

        if (is_null($pesquisa->primeiroContato)) {
            Log::info("Enviando primeira mensagem para {$this->numeroWhats}");

            $pergunta = PerguntaPesquisa::where('pesquisa', 'smsa')
                ->where('nome', 'autorizacaoLGPD')
                ->first();

            if (!$pergunta) {
                Log::warning("Nenhuma pergunta LGPD encontrada.");
                return;
            }

            if ($evolution->enviaWhats($this->numeroWhats, $pergunta->mensagem)) {
                $pesquisa->primeiroContato = 'não';
                $pesquisa->save();
                Log::info("Mensagem enviada com sucesso para {$this->numeroWhats}");
            } else {
                Log::error("Falha ao enviar mensagem para {$this->numeroWhats}. Reagendando tentativa.");
            }
            return;
        }

        if (is_null($pesquisa->autorizacaoLGPD)) {
            Log::info("Aguardando resposta de autorização LGPD.");
            $mensagens = $this->buscaUltimasMensagens($this->numeroWhats);
            if ($mensagens) {
                Log::info("Mensagens recebidas: {$mensagens}");

                $pesquisa->autorizacaoLGPD = $bot->promptBot($mensagens, 'lgpdAutorizacaoBOT');
                Log::info("$pesquisa->autorizacaoLGPD:", [$pesquisa->autorizacaoLGPD]);
                if ($pesquisa->autorizacaoLGPD != 'sim') {
                    Log::info("Usuário NÃO autorizou a pesquisa. Agradecendo...");
                    // 🔹 Se o usuário não autorizou a pesquisa, envia agradecimento e remove o telefone
                    $agradecimento = PerguntaPesquisa::where('pesquisa', 'smsa')
                        ->where('nome', 'lgpdNegado')
                        ->first();

                    if ($agradecimento) {
                        $evolution->enviaWhats($this->numeroWhats, $agradecimento->mensagem);
                    }
                    $pesquisa->autorizacaoLGPD = 'não';
                    $pesquisa->numeroWhats = null;
                } else {
                    Log::info("Usuário AUTORIZOU a pesquisa. Enviando próxima pergunta...");
                    // 🔹 Se o usuário autorizou a pesquisa, envia a primeira pergunta
                    $pergunta_nomeUnidadeSaude = PerguntaPesquisa::where('pesquisa', 'smsa')
                        ->where('nome', 'nomeUnidadeSaude')
                        ->first();

                    if ($pergunta_nomeUnidadeSaude) {
                        $evolution->enviaWhats($this->numeroWhats, $pergunta_nomeUnidadeSaude->mensagem);
                    }
                }
                $pesquisa->save();
            }
            Log::info("Encerrado.");
            return;
        }
    }

    private function buscaUltimasMensagens($numeroWhats)
    {
        return 'nao quero';
    }
}


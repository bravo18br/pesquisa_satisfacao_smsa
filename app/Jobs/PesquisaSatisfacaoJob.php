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
    protected $tentativasMaximas = 100; // 🔹 Evita loop infinito

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
            $mensagens = $this->buscaUltimasMensagens($this->numeroWhats);
            if ($mensagens) {
                $bot = new BotsController();
                $pesquisa->autorizacaoLGPD = $bot->promptBot($mensagens, 'lgpdAutorizacaoBOT');

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
                    $pesquisa->numeroWhats = null;
                } else {
                    // 🔹 Se o usuário autorizou a pesquisa, envia a primeira pergunta
                    $pergunta_nomeUnidadeSaude = PerguntaPesquisa::where('pesquisa', 'smsa')
                        ->where('nome', 'nomeUnidadeSaude')
                        ->first();

                    if ($pergunta_nomeUnidadeSaude) {
                        $evolution = new EvolutionController();
                        $evolution->enviaWhats($this->numeroWhats, $pergunta_nomeUnidadeSaude->mensagem);
                    }
                }
                $pesquisa->save();
            }
            return $this->release(10);
        }

        while ($pesquisa->nomeUnidadeSaude === null) {
            $mensagens = $this->buscaUltimasMensagens($this->numeroWhats);
            if ($mensagens) {
                $bot = new BotsController();
                $pesquisa->nomeUnidadeSaude = $bot->promptBot($mensagens, 'unidadeAtendimentoBOT');
                $pesquisa->save();

                $pergunta_recepcaoUnidade = PerguntaPesquisa::where('pesquisa', 'smsa')
                    ->where('nome', 'recepcaoUnidade')
                    ->first();

                if ($pergunta_recepcaoUnidade) {
                    $evolution = new EvolutionController();
                    $evolution->enviaWhats($this->numeroWhats, $pergunta_recepcaoUnidade->mensagem);
                }
            }
            return $this->release(10);
        }

        while ($pesquisa->recepcaoUnidade === null) {
            $mensagens = $this->buscaUltimasMensagens($this->numeroWhats);
            if ($mensagens) {
                $bot = new BotsController();
                $pesquisa->recepcaoUnidade = $bot->promptBot($mensagens, 'recepcaoUnidadeBOT');
                $pesquisa->save();

                $pergunta_limpezaUnidade = PerguntaPesquisa::where('pesquisa', 'smsa')
                    ->where('nome', 'limpezaUnidade')
                    ->first();

                if ($pergunta_limpezaUnidade) {
                    $evolution = new EvolutionController();
                    $evolution->enviaWhats($this->numeroWhats, $pergunta_limpezaUnidade->mensagem);
                }
            }
            return $this->release(10);
        }

        while ($pesquisa->limpezaUnidade === null) {
            $mensagens = $this->buscaUltimasMensagens($this->numeroWhats);
            if ($mensagens) {
                $bot = new BotsController();
                $pesquisa->limpezaUnidade = $bot->promptBot($mensagens, 'limpezaConservacaoBOT');
                $pesquisa->save();

                $pergunta_medicoQualidade = PerguntaPesquisa::where('pesquisa', 'smsa')
                    ->where('nome', 'medicoQualidade')
                    ->first();

                if ($pergunta_medicoQualidade) {
                    $evolution = new EvolutionController();
                    $evolution->enviaWhats($this->numeroWhats, $pergunta_medicoQualidade->mensagem);
                }
            }
            return $this->release(10);
        }

        while ($pesquisa->medicoQualidade === null) {
            $mensagens = $this->buscaUltimasMensagens($this->numeroWhats);
            if ($mensagens) {
                $bot = new BotsController();
                $pesquisa->medicoQualidade = $bot->promptBot($mensagens, 'medicoQualidadeBOT');
                $pesquisa->save();

                $pergunta_exameQualidade = PerguntaPesquisa::where('pesquisa', 'smsa')
                    ->where('nome', 'exameQualidade')
                    ->first();

                if ($pergunta_exameQualidade) {
                    $evolution = new EvolutionController();
                    $evolution->enviaWhats($this->numeroWhats, $pergunta_exameQualidade->mensagem);
                }
            }
            return $this->release(10);
        }

        while ($pesquisa->exameQualidade === null) {
            $mensagens = $this->buscaUltimasMensagens($this->numeroWhats);
            if ($mensagens) {
                $bot = new BotsController();
                $pesquisa->exameQualidade = $bot->promptBot($mensagens, 'exameQualidadeBOT');
                $pesquisa->save();

                $pergunta_tempoAtendimento = PerguntaPesquisa::where('pesquisa', 'smsa')
                    ->where('nome', 'tempoAtendimento')
                    ->first();

                if ($pergunta_tempoAtendimento) {
                    $evolution = new EvolutionController();
                    $evolution->enviaWhats($this->numeroWhats, $pergunta_tempoAtendimento->mensagem);
                }
            }
            return $this->release(10);
        }

        while ($pesquisa->tempoAtendimento === null) {
            $mensagens = $this->buscaUltimasMensagens($this->numeroWhats);
            if ($mensagens) {
                $bot = new BotsController();
                $pesquisa->tempoAtendimento = $bot->promptBot($mensagens, 'tempoAtendimentoBOT');
                $pesquisa->save();

                $pergunta_comentarioLivre = PerguntaPesquisa::where('pesquisa', 'smsa')
                    ->where('nome', 'comentarioLivre')
                    ->first();

                if ($pergunta_comentarioLivre) {
                    $evolution = new EvolutionController();
                    $evolution->enviaWhats($this->numeroWhats, $pergunta_comentarioLivre->mensagem);
                }
            }
            return $this->release(10);
        }

        while ($pesquisa->comentarioLivre === null) {
            $mensagens = $this->buscaUltimasMensagens($this->numeroWhats);
            if ($mensagens) {
                $bot = new BotsController();
                $pesquisa->comentarioLivre = $bot->promptBot($mensagens, 'comentarioLivreBOT');
                $pesquisa->save();
            }
            return $this->release(10);
        }

        if ($pesquisa->comentarioLivre != null) {
            $bot = new BotsController();
            $encerramento = $bot->promptBot($pesquisa->comentarioLivre, 'encerramentoPesquisaBOT');
            $evolution = new EvolutionController();
            $evolution->enviaWhats($this->numeroWhats, $encerramento);
            $pesquisa->numeroWhats = null;
            $pesquisa->save();
            return;
        }
    }
    private function buscaUltimasMensagens($numeroWhats)
    {
        $mensagensAlvo = EvolutionEvent::where('fromMe', false)
            ->where('remoteJid', $this->numeroWhats)
            ->pluck('conversation')
            ->filter()
            ->implode(' ');

        // 🔹 Obtém os IDs das mensagens processadas para exclusão
        $mensagensIds = EvolutionEvent::where('fromMe', false)
            ->where('remoteJid', $this->numeroWhats)
            ->pluck('id');
        EvolutionEvent::whereIn('id', $mensagensIds)->delete();

        return $mensagensAlvo;
    }
}

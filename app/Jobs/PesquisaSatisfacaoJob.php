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
use App\Models\EvolutionEvent;

class PesquisaSatisfacaoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $telefonePesquisa;

    public function __construct($telefonePesquisa)
    {
        $this->telefonePesquisa = $telefonePesquisa;
    }

    public function handle()
    {
        $evolution = new EvolutionController();
        $bot = new BotsController();

        $pesquisa = ProcessadaPesquisa::where('numeroWhats', $this->telefonePesquisa)
            ->where('pesquisaConcluida', false)
            ->first();

        if (!$pesquisa) {
            return;
        }

        if (is_null($pesquisa->primeiroContato)) {
            $pergunta = PerguntaPesquisa::where('pesquisa', 'smsa')
                ->where('nome', 'autorizacaoLGPD')
                ->first();

            if (!$pergunta) {
                return;
            }

            if ($evolution->enviaWhats($this->telefonePesquisa, $pergunta->mensagem)) {
                $pesquisa->primeiroContato = 'não';
                $pesquisa->save();
            }
            return;
        }

        if (is_null($pesquisa->autorizacaoLGPD)) {
            $mensagens = $this->buscaUltimasMensagens($this->telefonePesquisa);
            if ($mensagens) {
                $responseBot = $bot->promptBot($mensagens, 'lgpdAutorizacaoBOT');
                $responseBotData = $responseBot->getData(true); // Converte para array associativo
                $response = $responseBotData['response'] ?? null; // Obtém a chave 'response'

                if (!$response) {
                    return;
                }

                $pesquisa->autorizacaoLGPD = $response;

                if ($pesquisa->autorizacaoLGPD != 'sim') {
                    $agradecimento = PerguntaPesquisa::where('pesquisa', 'smsa')
                        ->where('nome', 'lgpdNegado')
                        ->first();

                    if ($agradecimento) {
                        $evolution->enviaWhats($this->telefonePesquisa, $agradecimento->mensagem);
                    }
                    $pesquisa->numeroWhats = null;
                    $pesquisa->pesquisaConcluida = true;
                    $pesquisa->save();
                    return 0;
                } else {
                    $pergunta_nomeUnidadeSaude = PerguntaPesquisa::where('pesquisa', 'smsa')
                        ->where('nome', 'nomeUnidadeSaude')
                        ->first();

                    if ($pergunta_nomeUnidadeSaude) {
                        $evolution->enviaWhats($this->telefonePesquisa, $pergunta_nomeUnidadeSaude->mensagem);
                    }
                }
                $pesquisa->save();
            }
            return;
        }

        if (is_null($pesquisa->nomeUnidadeSaude)) {
            $mensagens = $this->buscaUltimasMensagens($this->telefonePesquisa);
            if ($mensagens) {
                $responseBot = $bot->promptBot($mensagens, 'unidadeAtendimentoBOT');
                $responseBotData = $responseBot->getData(true); // Converte para array associativo
                $response = $responseBotData['response'] ?? null; // Obtém a chave 'response'

                if (!$response) {
                    return;
                }

                $pesquisa->nomeUnidadeSaude = $response;
                $pesquisa->save();

                $pergunta_recepcaoUnidade = PerguntaPesquisa::where('pesquisa', 'smsa')
                    ->where('nome', 'recepcaoUnidade')
                    ->first();

                if ($pergunta_recepcaoUnidade) {
                    $evolution->enviaWhats($this->telefonePesquisa, $pergunta_recepcaoUnidade->mensagem);
                }

            }
            return;
        }

        if (is_null($pesquisa->recepcaoUnidade)) {
            $mensagens = $this->buscaUltimasMensagens($this->telefonePesquisa);
            if ($mensagens) {
                $responseBot = $bot->promptBot($mensagens, 'recepcaoUnidadeBOT');
                $responseBotData = $responseBot->getData(true); // Converte para array associativo
                $response = $responseBotData['response'] ?? null; // Obtém a chave 'response'

                if (!$response) {
                    return;
                }

                $pesquisa->recepcaoUnidade = $response;
                $pesquisa->save();

                $pergunta_limpezaUnidade = PerguntaPesquisa::where('pesquisa', 'smsa')
                    ->where('nome', 'limpezaUnidade')
                    ->first();

                if ($pergunta_limpezaUnidade) {
                    $evolution->enviaWhats($this->telefonePesquisa, $pergunta_limpezaUnidade->mensagem);
                }

            }
            return;
        }

        if (is_null($pesquisa->limpezaUnidade)) {
            $mensagens = $this->buscaUltimasMensagens($this->telefonePesquisa);
            if ($mensagens) {
                $responseBot = $bot->promptBot($mensagens, 'limpezaConservacaoBOT');
                $responseBotData = $responseBot->getData(true); // Converte para array associativo
                $response = $responseBotData['response'] ?? null; // Obtém a chave 'response'

                if (!$response) {
                    return;
                }

                $pesquisa->limpezaUnidade = $response;
                $pesquisa->save();

                $pergunta_medicoQualidade = PerguntaPesquisa::where('pesquisa', 'smsa')
                    ->where('nome', 'medicoQualidade')
                    ->first();

                if ($pergunta_medicoQualidade) {
                    $evolution->enviaWhats($this->telefonePesquisa, $pergunta_medicoQualidade->mensagem);
                }

            }
            return;
        }

        if (is_null($pesquisa->medicoQualidade)) {
            $mensagens = $this->buscaUltimasMensagens($this->telefonePesquisa);
            if ($mensagens) {
                $responseBot = $bot->promptBot($mensagens, 'medicoQualidadeBOT');
                $responseBotData = $responseBot->getData(true); // Converte para array associativo
                $response = $responseBotData['response'] ?? null; // Obtém a chave 'response'

                if (!$response) {
                    return;
                }

                $pesquisa->medicoQualidade = $response;
                $pesquisa->save();

                $pergunta_exameQualidade = PerguntaPesquisa::where('pesquisa', 'smsa')
                    ->where('nome', 'exameQualidade')
                    ->first();

                if ($pergunta_exameQualidade) {
                    $evolution->enviaWhats($this->telefonePesquisa, $pergunta_exameQualidade->mensagem);
                }
            }
            return;
        }

        if (is_null($pesquisa->exameQualidade)) {
            $mensagens = $this->buscaUltimasMensagens($this->telefonePesquisa);
            if ($mensagens) {
                $responseBot = $bot->promptBot($mensagens, 'exameQualidadeBOT');
                $responseBotData = $responseBot->getData(true); // Converte para array associativo
                $response = $responseBotData['response'] ?? null; // Obtém a chave 'response'

                if (!$response) {
                    return;
                }

                $pesquisa->exameQualidade = $response;
                $pesquisa->save();

                $pergunta_tempoAtendimento = PerguntaPesquisa::where('pesquisa', 'smsa')
                    ->where('nome', 'tempoAtendimento')
                    ->first();

                if ($pergunta_tempoAtendimento) {
                    $evolution->enviaWhats($this->telefonePesquisa, $pergunta_tempoAtendimento->mensagem);
                }
            }
            return;
        }

        if (is_null($pesquisa->tempoAtendimento)) {
            $mensagens = $this->buscaUltimasMensagens($this->telefonePesquisa);

            if ($mensagens) {
                $responseBot = $bot->promptBot($mensagens, 'tempoAtendimentoBOT');
                $responseBotData = $responseBot->getData(true);
                $response = $responseBotData['response'] ?? null;

                if (!$response) {
                    return;
                }

                $pesquisa->tempoAtendimento = $response;
                $pesquisa->save();

                $pergunta_comentarioLivre = PerguntaPesquisa::where('pesquisa', 'smsa')
                    ->where('nome', 'comentarioLivre')
                    ->first();

                if ($pergunta_comentarioLivre) {
                    $evolution->enviaWhats($this->telefonePesquisa, $pergunta_comentarioLivre->mensagem);
                }
            }
            return;
        }

        if (is_null($pesquisa->comentarioLivre)) {
            $mensagens = $this->buscaUltimasMensagens($this->telefonePesquisa);

            if ($mensagens) {
                $responseBot = $bot->promptBot($mensagens, 'comentarioLivreBOT');
                $responseBotData = $responseBot->getData(true);
                $response = $responseBotData['response'] ?? null;

                if (!$response) {
                    return;
                }

                $pesquisa->comentarioLivre = $response;
                $pesquisa->save();

                $encerramentoBot = $bot->promptBot($response, 'encerramentoPesquisaBOT');
                $encerramentoBotData = $encerramentoBot->getData(true);
                $encerramento = $encerramentoBotData['response'] ?? null;

                $evolution->enviaWhats($this->telefonePesquisa, $encerramento);

                $pesquisa->pesquisaConcluida = true;
                $pesquisa->numeroWhats = null;
                $pesquisa->save();

            }
            return;
        }
    }

    private function buscaUltimasMensagens($telefonePesquisa)
    {
        $mensagensAlvo = EvolutionEvent::all();
        $resposta = '';

        foreach ($mensagensAlvo as $alvo) {
            $registro = json_decode($alvo, true);

            // Garante que a estrutura do JSON existe
            if (!isset($registro['data']['data']['key']['remoteJid']) || !isset($registro['data']['data']['message']['conversation'])) {
                continue; // Pula se os dados necessários não existirem
            }

            $remoteJid = $registro['data']['data']['key']['remoteJid'];
            $conversation = $registro['data']['data']['message']['conversation']; // Correção aqui
            $numeroFormatado8 = $this->formatarNumeroWhatsApp8($telefonePesquisa);
            $numeroFormatado9 = $this->formatarNumeroWhatsApp9($telefonePesquisa);

            if ($remoteJid === $numeroFormatado8 || $remoteJid === $numeroFormatado9) {
                $resposta .= $conversation . ' ';
                $alvo->delete();
            }
        }

        return trim($resposta); // Remove espaços extras no final
    }

    private function formatarNumeroWhatsApp8(string $numero): string
    {
        $numero = preg_replace('/\D/', '', $numero); // Remove tudo que não for número
        $numero = ltrim($numero, '0'); // Remove zeros à esquerda

        if (strlen($numero) < 10 || strlen($numero) > 12) {
            throw new \InvalidArgumentException("Número inválido: $numero");
        }

        // Se o telefone tiver 9 dígitos, remove o primeiro (para ficar com 8)
        if (strlen($numero) === 11 || strlen($numero) === 12) {
            $ddd = substr($numero, 0, 2);
            $telefone = substr($numero, 3); // Remove o primeiro dígito do telefone (o "9")
        } else {
            $ddd = substr($numero, 0, 2);
            $telefone = substr($numero, 2);
        }

        return "55{$ddd}{$telefone}@s.whatsapp.net";
    }

    private function formatarNumeroWhatsApp9(string $numero): string
    {
        $numero = preg_replace('/\D/', '', $numero);
        $numero = ltrim($numero, '0');

        if (strlen($numero) < 10 || strlen($numero) > 12) {
            throw new \InvalidArgumentException("Número inválido: $numero");
        }

        // Se o telefone já tiver 9 dígitos, mantém
        if (strlen($numero) === 11 || strlen($numero) === 12) {
            $ddd = substr($numero, 0, 2);
            $telefone = substr($numero, 2);
        } else {
            // Se tiver apenas 8 dígitos, adiciona um "9" na frente
            $ddd = substr($numero, 0, 2);
            $telefone = '9' . substr($numero, 2);
        }

        return "55{$ddd}{$telefone}@s.whatsapp.net";
    }

}


<?php

namespace App\Jobs;

use App\Http\Controllers\EvolutionController;
use App\Http\Controllers\OllamaController;
use App\Models\EvolutionEvent;
use App\Models\RespostaPesquisa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PesquisaSatisfacaoSMSAJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $telefonePesquisa;
    protected $evolution;
    protected $ollama;
    protected $respostas = [];

    protected $perguntas = [
        'autorizacaoLGPD' => 'Sou Paulo da Secretaria Municipal de Saúde de Araucária PR! Você me autoriza a fazer uma pesquisa de satisfação sobre o seu atendimento médico de hoje? ( Sim / Não )',
        'unidadeAtendimento' => 'Qual o nome da unidade de saúde em que você esteve recentemente?',
        'recepcaoUnidade' => 'Você gostou da recepção na unidade?',
        'limpezaConservacao' => 'Como você avalia a limpeza e conservação da unidade de saúde?',
        'medicoQualidade' => 'O médico que lhe atendeu foi educado e prestativo? Como você avalia ele?',
        'exameQualidade' => 'Você fez algum exame? Foi bem executado?',
        'tempoAtendimento' => 'Como você avalia o tempo de espera para ser atendido? Foi rápido ou demorou?',
        'comentarioLivre' => 'Deixe um comentário sobre o atendimento em geral, você está satisfeito?',
    ];

    public function __construct($telefonePesquisa)
    {
        $this->telefonePesquisa = $telefonePesquisa;
    }

    public function handle()
    {
        Log::info('JOB para telefone ' . $this->telefonePesquisa . ' iniciado.');

        $pesquisa = RespostaPesquisa::where('numeroWhats', $this->telefonePesquisa)
            ->where('pesquisaConcluida', false)
            ->first();

        $evolution = app(EvolutionController::class);
        $ollama = app(OllamaController::class);

        // Fazer pergunta autorizacaoLGPD
        $this->fazerPergunta('autorizacaoLGPD', $evolution);

        // Registrar resposta da autorizacaoLGPD
        $this->registrarResposta('autorizacaoLGPD', $pesquisa);

        // Avaliar resposta da autorizacaoLGPD
        $this->avaliarResposta_autorizacaoLGPD_BOT($pesquisa, $evolution, $ollama);

        // Fazer pergunta unidadeAtendimento
        $this->fazerPergunta('unidadeAtendimento', $evolution);

        // Registrar resposta da unidadeAtendimento
        $this->registrarResposta('unidadeAtendimento', $pesquisa);

        // Avaliar resposta da unidadeAtendimento
        $this->avaliarResposta_unidadeAtendimento_BOT($evolution, $ollama);

        // Fazer pergunta recepcaoUnidade
        $this->fazerPergunta('recepcaoUnidade', $evolution);

        // Registrar resposta da recepcaoUnidade
        $this->registrarResposta('recepcaoUnidade', $pesquisa);

        // Fazer pergunta limpezaConservacao
        $this->fazerPergunta('limpezaConservacao', $evolution);

        // Registrar resposta da limpezaConservacao
        $this->registrarResposta('limpezaConservacao', $pesquisa);

        // Fazer pergunta medicoQualidade
        $this->fazerPergunta('medicoQualidade', $evolution);

        // Registrar resposta da medicoQualidade
        $this->registrarResposta('medicoQualidade', $pesquisa);

        // Fazer pergunta exameQualidade
        $this->fazerPergunta('exameQualidade', $evolution);

        // Registrar resposta da exameQualidade
        $this->registrarResposta('exameQualidade', $pesquisa);

        // Fazer pergunta tempoAtendimento
        $this->fazerPergunta('tempoAtendimento', $evolution);

        // Registrar resposta da tempoAtendimento
        $this->registrarResposta('tempoAtendimento', $pesquisa);

        // Fazer pergunta comentarioLivre
        $this->fazerPergunta('comentarioLivre', $evolution);

        // Registrar resposta da comentarioLivre
        $this->registrarResposta('comentarioLivre', $pesquisa);

        //encerrar a pesquisa
        $this->encerramentoBOT($evolution, $ollama);

        Log::info('JOB para telefone ' . $this->telefonePesquisa . ' encerrado.');
        return;
    }

    private function encerramentoBOT($evolution, $ollama)
    {
        try {
            $prompt = '';
            $prompt .= "<|start_pesquisador_pergunta|>{$this->perguntas['comentarioLivre']}<|end_pesquisador_pergunta|>\n";
            $prompt .= "<|start_usuario_resposta|>{$this->respostas['comentarioLivre']}<|end_usuario_resposta|>\n";
            $prompt .= "<|start_prompt|>Responda o usuário, agradeça e encerre a pesquisa em nome da Prefeitura de Araucaria.<|end_prompt|>";

            $params = [
                "model" => 'llama3.1',
                "messages" => [
                    [
                        "role" => "system",
                        "content" => "Você é Paulo, e está encerrando uma pesquisa de satisfação. Responda o prompt."
                    ],
                    [
                        "role" => "user",
                        "content" => $prompt
                    ]
                ],
                "stream" => false,
                "max_length" => 4,
                "options" => [
                    "temperature" => 0.0,
                    "top_p" => 0.1,
                ]
            ];

            $response = $ollama->chatOllama($params);
            if (isset($response['message']['content'])) {
                $evolution->enviaWhats($this->telefonePesquisa, "OK, {$this->perguntas['unidadeAtendimento']}, anotado.");
                $evolution->enviaWhats($this->telefonePesquisa, "Vamos para a próxima pergunta.");
                Log::info('Resposta unidadeAtendimento recebida.');
            }

        } catch (\Exception $e) {
            Log::error('Erro ao avaliar resposta da unidadeAtendimento');
            return;
        }
    }

    private function avaliarResposta_unidadeAtendimento_BOT($evolution, $ollama)
    {
        try {
            $prompt = '';
            $prompt .= "<|start_pesquisador_pergunta|>{$this->perguntas['unidadeAtendimento']}<|end_pesquisador_pergunta|>\n";
            $prompt .= "<|start_usuario_resposta|>{$this->capturarResposta('unidadeAtendimento', $evolution)}<|end_usuario_resposta|>\n";
            $prompt .= "<|start_prompt|>Qual o nome da unidade de saúde que o usuário informou? Somente o nome da unidade, mais nada.<|end_prompt|>";

            $params = [
                "model" => 'llama3.1',
                "messages" => [
                    [
                        "role" => "system",
                        "content" => "Você é Paulo, e está fazendo uma pesquisa de satisfação. Responda o prompt."
                    ],
                    [
                        "role" => "user",
                        "content" => $prompt
                    ]
                ],
                "stream" => false,
                "max_length" => 4,
                "options" => [
                    "temperature" => 0.0,
                    "top_p" => 0.1,
                ]
            ];

            $response = $ollama->chatOllama($params);
            if (isset($response['message']['content'])) {
                $evolution->enviaWhats($this->telefonePesquisa, "OK, {$this->perguntas['unidadeAtendimento']}, anotado.");
                $evolution->enviaWhats($this->telefonePesquisa, "Vamos para a próxima pergunta.");
                Log::info('Resposta unidadeAtendimento recebida.');
            }

        } catch (\Exception $e) {
            Log::error('Erro ao avaliar resposta da unidadeAtendimento');
            return;
        }
    }

    private function avaliarResposta_autorizacaoLGPD_BOT($pesquisa, $evolution, $ollama)
    {
        try {
            $prompt = '';
            $prompt .= "<|start_pesquisador_pergunta|>{$this->perguntas['autorizacaoLGPD']}<|end_pesquisador_pergunta|>\n";
            $prompt .= "<|start_usuario_resposta|>{$this->capturarResposta('autorizacaoLGPD', $evolution)}<|end_usuario_resposta|>\n";
            $prompt .= "<|start_prompt|>o usuário autorizou a pesquisa? responda apenas 'sim' ou 'não'. no-caps. sem pontos, nem aspas.<|end_prompt|>";

            $params = [
                "model" => 'llama3.1',
                "messages" => [
                    [
                        "role" => "system",
                        "content" => "Você é Paulo, e está fazendo uma pesquisa de satisfação. Responda o prompt."
                    ],
                    [
                        "role" => "user",
                        "content" => $prompt
                    ]
                ],
                "stream" => false,
                "max_length" => 4,
                "options" => [
                    "temperature" => 0.0,
                    "top_p" => 0.2,
                ]
            ];

            $response = $ollama->chatOllama($params);
            if (isset($response['message']['content']) && $response['message']['content'] === 'sim') {
                // Agradecer por ter aceitado participar da pesquisa
                $evolution->enviaWhats($this->telefonePesquisa, 'Excelente notícia!');
                $evolution->enviaWhats($this->telefonePesquisa, 'A pesquisa é anônima, e as respostas serão utilizadas para melhorar os servições de saúde da cidade.');
                $evolution->enviaWhats($this->telefonePesquisa, 'Vou mandar a primeira pergunta então!');
                Log::info('Pesquisa iniciada, LGPD autorizado.');
            } else {
                // Agradecer por ter respondido e encerrar a pesquisa
                $mensagemLGPDNegada = 'Está sem tempo agora? Sem problemas, deixamos para outro momento. Obrigado por responder!';
                if ($evolution->enviaWhats($this->telefonePesquisa, $mensagemLGPDNegada)) {
                    $pesquisa->pesquisaConcluida = true;
                    $pesquisa->numeroWhats = null;
                    $pesquisa->save();
                    Log::info('Pesquisa encerrada, LGPD não autorizado.');
                } else {
                    Log::error('Erro ao enviar mensagem de LGPD negado.');
                }
                return;
            }

        } catch (\Exception $e) {
            Log::error('Erro ao avaliar resposta da autorização');
            return;
        }
    }

    private function fazerPergunta($pergunta, $evolution)
    {
        try {
            if ($evolution->enviaWhats($this->telefonePesquisa, $this->perguntas[$pergunta])) {
                $this->respostas[$pergunta] = 'aguardando';
                Log::info('Mensagem de {$pergunta} enviada.');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao fazer pergunta {$pergunta}');
            return;
        }
    }

    private function registrarResposta($pergunta, $pesquisa)
    {
        try {
            $pesquisa->recepcaoUnidade = $this->respostas[$pergunta];
            $pesquisa->save();
            Log::info("Resposta {$pergunta} registrada");
        } catch (\Exception $e) {
            Log::error('Erro ao registrar resposta da {$pergunta}');
            return;
        }
    }

    private function capturarResposta($pergunta, $evolution)
    {
        try {
            $tempoInicio = microtime(true);
            $tempoLimite = 600; // 10 minutos em segundos

            while ($this->respostas[$pergunta] === 'aguardando') {
                $mensagem = $this->buscaUltimasMensagens($this->telefonePesquisa);

                if ($mensagem) {
                    $this->respostas[$pergunta] = $mensagem;
                    break;
                }

                // Verifica se passou do tempo limite de 10 minutos
                if ((microtime(true) - $tempoInicio) > $tempoLimite) {
                    $evolution->enviaWhats($this->telefonePesquisa, 'Pesquisa encerrada por falta de atividade.');
                    Log::error("Tempo limite excedido (10 minutos) esperando resposta do telefone: " . $this->telefonePesquisa);
                    return;
                }
                sleep(2);
            }
            return $this->respostas[$pergunta];
        } catch (\Exception $e) {
            Log::error('Erro ao capturar a resposta da autorização com timeout de 10 minutos');
            return null;
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

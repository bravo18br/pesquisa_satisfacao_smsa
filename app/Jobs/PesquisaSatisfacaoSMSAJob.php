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
    protected $respostas;

    protected $perguntas = [
        'autorizacaoLGPD' => "Sou Paulo da Secretaria Municipal de Saúde de Araucária PR!" . PHP_EOL . "Você me autoriza a fazer uma pesquisa de satisfação sobre o seu atendimento médico de hoje?" . PHP_EOL . "( Sim / Não )",
        'nomeUnidadeSaude' => 'Qual o nome da unidade de saúde em que você esteve recentemente?',
        'recepcaoUnidade' => 'Você gostou da recepção na unidade?',
        'limpezaUnidade' => 'Como você avalia a limpeza e conservação da unidade de saúde?',
        'medicoQualidade' => "O médico que lhe atendeu foi educado e prestativo?" . PHP_EOL . "Como você avalia ele?" . PHP_EOL . "Qual o nome dele?",
        'exameQualidade' => "Você fez algum exame?" . PHP_EOL . "Foi bem executado?",
        'pontualidadeAtendimento' => "Como você avalia o tempo de espera para ser atendido?" . PHP_EOL . "Foi rápido ou demorou?",
        'observacaoLivre' => 'Deixe um comentário sobre o atendimento em geral, você está satisfeito?',
    ];

    public function __construct($telefonePesquisa)
    {
        $this->telefonePesquisa = $telefonePesquisa;
    }

    public function handle()
    {
        Log::info('JOB para telefone ' . $this->telefonePesquisa . ' iniciado');

        $pesquisa = RespostaPesquisa::where('numeroWhats', $this->telefonePesquisa)
            ->where('pesquisaConcluida', false)
            ->first();

        $evolution = app(EvolutionController::class);
        $ollama = app(OllamaController::class);
        $respostas = [];

        // Fazer pergunta autorizacaoLGPD
        $this->fazerPergunta('autorizacaoLGPD', $evolution, $pesquisa, $respostas);

        // Registrar resposta da autorizacaoLGPD
        $this->capturarResposta('autorizacaoLGPD', $evolution, $pesquisa, $respostas);

        // Avaliar resposta da autorizacaoLGPD
        if (!$this->avaliarResposta_autorizacaoLGPD_BOT($evolution, $ollama, $pesquisa, $respostas)) {
            Log::info('JOB para telefone ' . $this->telefonePesquisa . ' encerrado');
            return;
        }

        // Fazer pergunta nomeUnidadeSaude
        $this->fazerPergunta('nomeUnidadeSaude', $evolution, $pesquisa, $respostas);

        // Registrar resposta da nomeUnidadeSaude
        $this->capturarResposta('nomeUnidadeSaude', $evolution, $pesquisa, $respostas);

        // Avaliar resposta da nomeUnidadeSaude
        $this->avaliarResposta_nomeUnidadeSaude_BOT($evolution, $ollama, $respostas);

        // Fazer pergunta recepcaoUnidade
        $this->fazerPergunta('recepcaoUnidade', $evolution, $pesquisa, $respostas);

        // Registrar resposta da recepcaoUnidade
        $this->capturarResposta('recepcaoUnidade', $evolution, $pesquisa, $respostas);

        // Fazer pergunta limpezaUnidade
        $this->fazerPergunta('limpezaUnidade', $evolution, $pesquisa, $respostas);

        // Registrar resposta da limpezaUnidade
        $this->capturarResposta('limpezaUnidade', $evolution, $pesquisa, $respostas);

        // Fazer pergunta medicoQualidade
        $this->fazerPergunta('medicoQualidade', $evolution, $pesquisa, $respostas);

        // Registrar resposta da medicoQualidade
        $this->capturarResposta('medicoQualidade', $evolution, $pesquisa, $respostas);

        // Fazer pergunta exameQualidade
        $this->fazerPergunta('exameQualidade', $evolution, $pesquisa, $respostas);

        // Registrar resposta da exameQualidade
        $this->capturarResposta('exameQualidade', $evolution, $pesquisa, $respostas);

        // Fazer pergunta pontualidadeAtendimento
        $this->fazerPergunta('pontualidadeAtendimento', $evolution, $pesquisa, $respostas);

        // Registrar resposta da pontualidadeAtendimento
        $this->capturarResposta('pontualidadeAtendimento', $evolution, $pesquisa, $respostas);

        // Fazer pergunta observacaoLivre
        $this->fazerPergunta('observacaoLivre', $evolution, $pesquisa, $respostas);

        // Registrar resposta da observacaoLivre
        $this->capturarResposta('observacaoLivre', $evolution, $pesquisa, $respostas);

        //encerrar a pesquisa
        $this->encerramentoBOT($evolution, $ollama, $respostas, $pesquisa);

    }

    private function fazerPergunta($pergunta, $evolution, $pesquisa, &$respostas)
    {
        try {
            if ($evolution->enviaWhats($this->telefonePesquisa, $this->perguntas[$pergunta])) {
                $respostas[$pergunta] = 'aguardando';
                $pesquisa->$pergunta = 'aguardando';
                $pesquisa->save();
                Log::info("Mensagem de {$pergunta} enviada");
            }
        } catch (\Exception $e) {
            Log::error("Erro ao fazer pergunta {$pergunta}");
        }
    }

    private function encerramentoBOT($evolution, $ollama, &$respostas, $pesquisa)
    {
        try {
            $prompt = '';
            $prompt .= "<|start_pesquisador_pergunta|>{$this->perguntas['observacaoLivre']}<|end_pesquisador_pergunta|>\n";
            $prompt .= "<|start_usuario_resposta|>{$respostas['observacaoLivre']}<|end_usuario_resposta|>\n";
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
                "max_length" => 300,
                "options" => [
                    "temperature" => 0.0,
                    "top_p" => 0.1,
                ]
            ];

            $response = $ollama->chatOllama($params);
            if (isset($response['message']['content'])) {
                $evolution->enviaWhats($this->telefonePesquisa, $response['message']['content']);
                $pesquisa->pesquisaConcluida = true;
                $pesquisa->numeroWhats = null;
                $pesquisa->save();
                Log::info('EncerramentoBOT executado.');
            }
        } catch (\Exception $e) {
            Log::error('Erro no encerramentoBOT');
            return;
        }
    }

    private function avaliarResposta_nomeUnidadeSaude_BOT($evolution, $ollama, &$respostas)
    {
        try {
            $prompt = '';
            $prompt .= "<|start_pesquisador_pergunta|>{$this->perguntas['nomeUnidadeSaude']}<|end_pesquisador_pergunta|>\n";
            $prompt .= "<|start_usuario_resposta|>{$respostas['nomeUnidadeSaude']}<|end_usuario_resposta|>\n";
            $prompt .= "<|start_prompt|>Qual o nome da unidade de saúde que o usuário informou? Somente o nome da unidade, mais nada, sem pontuação.<|end_prompt|>";

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
                "max_length" => 30,
                "options" => [
                    "temperature" => 0.0,
                    "top_p" => 0.1,
                ]
            ];

            $response = $ollama->chatOllama($params);
            if (isset($response['message']['content'])) {
                $evolution->enviaWhats($this->telefonePesquisa, "OK, {$response['message']['content']}, anotado.");
                $evolution->enviaWhats($this->telefonePesquisa, "Vamos para a próxima pergunta.");
                Log::info('Avaliar nomeUnidadeSaude concluída.');
            }

        } catch (\Exception $e) {
            Log::error('Erro ao avaliar resposta da nomeUnidadeSaude: ' . $e->getMessage());
            return;
        }
    }

    private function avaliarResposta_autorizacaoLGPD_BOT($evolution, $ollama, $pesquisa, &$respostas)
    {
        try {
            $prompt = '';
            $prompt .= "<|start_pesquisador_pergunta|>{$this->perguntas['autorizacaoLGPD']}<|end_pesquisador_pergunta|>\n";
            $prompt .= "<|start_usuario_resposta|>{$respostas['autorizacaoLGPD']}<|end_usuario_resposta|>\n";
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
                $evolution->enviaWhats($this->telefonePesquisa, 'A pesquisa é anônima, e as respostas serão utilizadas para melhorar os serviços de saúde da cidade.');
                $evolution->enviaWhats($this->telefonePesquisa, 'Vou mandar a primeira pergunta então!');
                Log::info('Pesquisa iniciada, LGPD autorizado.');
                return true;
            } else {
                // Agradecer por ter respondido e encerrar a pesquisa
                $evolution->enviaWhats($this->telefonePesquisa, 'Está sem tempo agora?');
                $evolution->enviaWhats($this->telefonePesquisa, 'Sem problemas, deixamos para outro momento.');
                $evolution->enviaWhats($this->telefonePesquisa, 'Obrigado, até mais!');
                $pesquisa->pesquisaConcluida = true;
                $pesquisa->numeroWhats = null;
                $pesquisa->save();
                Log::info('Pesquisa encerrada, LGPD não autorizado.');
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Erro ao avaliar resposta da autorização: ' . $e->getMessage());
            return false;
        }
    }

    private function capturarResposta($pergunta, $evolution, $pesquisa, &$respostas)
    {
        try {
            $tempoInicio = microtime(true);
            $tempoLimite = 600; // 10 minutos em segundos

            while ($respostas[$pergunta] === 'aguardando') {
                $mensagem = $this->buscaUltimasMensagens($this->telefonePesquisa);

                if ($mensagem) {
                    $respostas[$pergunta] = $mensagem;
                    $pesquisa->$pergunta = $mensagem;
                    $pesquisa->save();
                    Log::info("Resposta {$pergunta} registrada");
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
            return $respostas[$pergunta];
        } catch (\Exception $e) {
            Log::error('Erro ao capturar a resposta com timeout de 10 minutos: ' . $e->getMessage());
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

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
        Log::info("Iniciando JOB para: {$this->telefonePesquisa}");
        $evolution = new EvolutionController();
        $bot = new BotsController();

        $pesquisa = ProcessadaPesquisa::where('numeroWhats', $this->telefonePesquisa)
            ->where('pesquisaConcluida', false)
            ->first();

        if (!$pesquisa) {
            Log::warning("Nenhuma pesquisa encontrada para {$this->telefonePesquisa}");
            return;
        }

        if (is_null($pesquisa->primeiroContato)) {
            Log::info("Enviando primeira mensagem para {$this->telefonePesquisa}");

            $pergunta = PerguntaPesquisa::where('pesquisa', 'smsa')
                ->where('nome', 'autorizacaoLGPD')
                ->first();

            if (!$pergunta) {
                Log::warning("Nenhuma pergunta LGPD encontrada.");
                return;
            }

            if ($evolution->enviaWhats($this->telefonePesquisa, $pergunta->mensagem)) {
                $pesquisa->primeiroContato = 'não';
                $pesquisa->save();
                Log::info("Mensagem enviada com sucesso para {$this->telefonePesquisa}");
            } else {
                Log::error("Falha ao enviar mensagem para {$this->telefonePesquisa}. Reagendando tentativa.");
            }
            return;
        }

        if (is_null($pesquisa->autorizacaoLGPD)) {
            Log::info("Aguardando resposta de autorização LGPD.");
            $mensagens = $this->buscaUltimasMensagens($this->telefonePesquisa);
            if ($mensagens) {
                Log::info("Mensagens recebidas: $mensagens");

                $pesquisa->autorizacaoLGPD = $bot->promptBot($mensagens, 'lgpdAutorizacaoBOT');
                Log::info("$pesquisa->autorizacaoLGPD:", [$pesquisa->autorizacaoLGPD]);
                if ($pesquisa->autorizacaoLGPD != 'sim') {
                    Log::info("Usuário NÃO autorizou a pesquisa. Agradecendo...");
                    // 🔹 Se o usuário não autorizou a pesquisa, envia agradecimento e remove o telefone
                    $agradecimento = PerguntaPesquisa::where('pesquisa', 'smsa')
                        ->where('nome', 'lgpdNegado')
                        ->first();

                    if ($agradecimento) {
                        $evolution->enviaWhats($this->telefonePesquisa, $agradecimento->mensagem);
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
                        $evolution->enviaWhats($this->telefonePesquisa, $pergunta_nomeUnidadeSaude->mensagem);
                    }
                }
                $pesquisa->save();
            }
            Log::info("Encerrado.");
            return;
        }
    }

    private function buscaUltimasMensagens($telefonePesquisa)
    {
        // Obtém as mensagens com base no remoteJid armazenado no banco
        $mensagensAlvo = EvolutionEvent::all();
        $resposta = '';

        foreach ($mensagensAlvo as $alvo) {
            $registro = json_decode($alvo, true);
            $data = $registro['data']['data'];

            if ($remoteJid === $this->formatarNumeroWhatsApp8($telefonePesquisa) || $remoteJid === $this->formatarNumeroWhatsApp9($telefonePesquisa)) {
                $resposta .= $data['message']['conversation'] . ' ';
            }
        }

        return $resposta;
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


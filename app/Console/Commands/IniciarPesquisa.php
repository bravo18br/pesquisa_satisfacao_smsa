<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\PesquisaSatisfacaoJob;
use App\Http\Controllers\EvolutionController;
use App\Models\TelefonePesquisa;
use App\Models\PerguntaPesquisa;

class IniciarPesquisa extends Command
{
    protected $signature = 'app:iniciar_pesquisa';
    protected $description = 'Busca telefones dos usuários para a pesquisa e cria um job para cada um.';

    public function handle()
    {
        // TELEFONE DE TESTE, INSERIR NO DB
        // $telefoneTeste = '4136141593';
        $telefoneTeste = '41984191656';
        if (!TelefonePesquisa::where('whats', $telefoneTeste)->exists()) {
            TelefonePesquisa::create(['whats' => $telefoneTeste]);
            $this->info("Número de teste {$telefoneTeste} inserido na base.");
        } else {
            $this->info("Número de teste {$telefoneTeste} já existe na base.");
        }

        $contatos = TelefonePesquisa::whereNotNull('whats')->get();

        if ($contatos->isEmpty()) {
            $this->info('Nenhum contato pendente encontrado.');
            return 0;
        }

        foreach ($contatos as $contato) {
            $numeroWhats = $this->formatarNumeroWhatsApp($contato['whats']);

            // 🔹 Corrigido erro na consulta
            $pergunta = PerguntaPesquisa::where('pesquisa', 'smsa')
                ->where('nome', 'autorizacaoLGPD')
                ->first();

            if (!$pergunta) {
                $this->warn("Nenhuma pergunta encontrada para o número: {$numeroWhats}");
                continue; // Pula para o próximo contato
            }

            $evolution = new EvolutionController();

            if ($evolution->enviaWhats($numeroWhats, $pergunta->mensagem)) {
                // 🔹 Apaga o contato APÓS confirmação do envio da mensagem
                $contato->delete();
                $this->info("Contato removido: {$numeroWhats}");

                // 🔹 Criando job APÓS deletar o contato
                dispatch(new PesquisaSatisfacaoJob($numeroWhats));
                $this->info("JOB criado para: {$numeroWhats}");
            } else {
                $this->warn("Falha ao enviar mensagem para: {$numeroWhats}");
            }
        }

        $this->info('Todas as pesquisas foram encaminhadas.');

        // Verifica se já existe um worker rodando antes de iniciar um novo
        if (stripos(PHP_OS, 'WIN') !== false) {
            // Ambiente Windows
            $process = shell_exec('tasklist /FI "IMAGENAME eq php.exe" /V | findstr /I "queue:work"');
            if (!$process) {
                $this->info('Iniciando o worker para processar os jobs (Windows)...');
                // No Windows, inicia em background usando start /B
                pclose(popen('start /B php artisan queue:work --tries=3 --timeout=60', 'r'));
            } else {
                $this->info('Worker já está rodando, não será iniciado novamente.');
            }
        } else {
            // Ambiente Linux/Unix
            $process = shell_exec('ps aux | grep "queue:work" | grep -v grep');
            if (!$process) {
                $this->info('Iniciando o worker para processar os jobs...');
                exec('php artisan queue:work --tries=3 --timeout=60 &');
            } else {
                $this->info('Worker já está rodando, não será iniciado novamente.');
            }
        }


        return 0;
    }

    private function formatarNumeroWhatsApp(string $numero): string
    {
        $numero = preg_replace('/\D/', '', $numero);
        $numero = ltrim($numero, '0');

        if (strlen($numero) === 10 || strlen($numero) === 11) {
            $ddd = substr($numero, 0, 2);
            $telefone = substr($numero, 2);
        } elseif (strlen($numero) === 12) {
            $ddd = substr($numero, 0, 3);
            $telefone = substr($numero, 3);
        } else {
            throw new \InvalidArgumentException("Número inválido: $numero");
        }

        return "55{$ddd}{$telefone}@s.whatsapp.net";
    }
}

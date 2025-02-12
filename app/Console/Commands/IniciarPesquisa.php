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
        $contatos = TelefonePesquisa::whereNotNull('whats')->get();

        if ($contatos->isEmpty()) {
            $this->info('Nenhum contato pendente encontrado.');
            return 0;
        }

        foreach ($contatos as $contato) {

            //Mandar a primeira pergunta, antes de iniciar o job
            $numeroWhats = $this->formatarNumeroWhatsApp($contato['whats']);

            $pergunta = PerguntaPesquisa::where('pesquisa' === 'smsa' || 'nome' === 'autorizacaoLGPD')->first();
            $pergunta = $pergunta['mensagem'];

            $evolution = new EvolutionController();

            if ($evolution->enviaWhats($numeroWhats, $pergunta)) {
                $this->info('Criando JOB para: ' . $numeroWhats);
                dispatch(new PesquisaSatisfacaoJob($numeroWhats));
            }
            ;
        }
        $this->info('Todas as pesquisas foram encaminhadas.');
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

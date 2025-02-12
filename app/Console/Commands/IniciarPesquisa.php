<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pesquisa;
use App\Jobs\EnviarPesquisaJob;

class IniciarPesquisa extends Command
{
    protected $signature = 'app:iniciar_pesquisa';
    protected $description = 'Busca telefones dos usuários para a pesquisa e cria um job para cada um.';

    public function handle()
    {
        $contatos = Pesquisa::whereNotNull('whats')->get();

        if ($contatos->isEmpty()) {
            $this->info('Nenhum contato pendente encontrado.');
            return Command::SUCCESS;
        }

        foreach ($contatos as $contato) {

            //Mandar a primeira pergunta, antes de iniciar o job
            $numero = $contato['whats'];
            $numeroWhats = $this->formatarNumeroWhatsApp($numero);
    
            $pergunta = PerguntaPesquisa::where('pesquisa'==='smsa' || 'nome'==='autorizacaoLGPD');
            $pergunta = $pergunta['mensagem'];
    
            $evolution = new EvolutionController();

            if ($evolution->enviaWhats($numeroWhats, $pergunta)){
                $this->info('Criando JOB para: ' . $numeroWhats);
    
                // Atualiza o status antes de disparar o job para evitar concorrência
                $contato->update(['status_pesquisa' => 'job iniciado']);
    
                dispatch(new EnviarPesquisaJob($contato));
            };
        }

        $this->info('Todas as pesquisas foram encaminhadas.');
        return Command::SUCCESS;
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

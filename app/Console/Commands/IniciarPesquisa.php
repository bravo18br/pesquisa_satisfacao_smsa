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
        $contatos = Pesquisa::whereNotNull('telefone')
            ->where('status_pesquisa', 'job não iniciado') // Evita reprocessamento
            ->get();

        if ($contatos->isEmpty()) {
            $this->info('Nenhum contato pendente encontrado.');
            return Command::SUCCESS;
        }

        foreach ($contatos as $contato) {
            $this->info('Criando JOB para: ' . $contato->telefone);

            // Atualiza o status antes de disparar o job para evitar concorrência
            $contato->update(['status_pesquisa' => 'job iniciado']);

            dispatch(new EnviarPesquisaJob($contato));
        }

        $this->info('Todas as pesquisas foram encaminhadas.');
        return Command::SUCCESS;
    }
}

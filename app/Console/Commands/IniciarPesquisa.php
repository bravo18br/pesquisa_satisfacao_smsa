<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\PesquisaSatisfacaoJob;
use Illuminate\Support\Facades\Log;

use App\Models\TelefonePesquisa;
use App\Models\ProcessadaPesquisa;

class IniciarPesquisa extends Command
{
    protected $signature = 'app:iniciar_pesquisa';
    protected $description = 'Busca telefones dos usuários para a pesquisa e cria um job para cada um.';

    public function handle()
    {
        // TELEFONE DE TESTE, INSERIR NO DB
        // $telefoneTeste = '4136141593';
        $telefoneTeste = '4184191656';
        if (!TelefonePesquisa::where('whats', $telefoneTeste)->exists()) {
            TelefonePesquisa::create(['whats' => $telefoneTeste]);
            Log::info("IniciarPesquisa.php - Número de teste {$telefoneTeste} inserido na base.");
        } else {
            Log::info("IniciarPesquisa.php - Número de teste {$telefoneTeste} já existe na base.");
        }


        $contatos = TelefonePesquisa::whereNotNull('whats')->get();

        if ($contatos->isEmpty()) {
            Log::info('IniciarPesquisa.php - Nenhum contato pendente encontrado.');
            return 0;
        }

        foreach ($contatos as $contato) {
            $existe = ProcessadaPesquisa::where('numeroWhats', $contato['whats'])
                ->where('pesquisaConcluida', false)
                ->first();

            if (!$existe) {
                ProcessadaPesquisa::create(['numeroWhats' => $contato['whats']]);
            }

            dispatch(new PesquisaSatisfacaoJob($contato['whats']));
            Log::info("IniciarPesquisa.php - JOB criado para: {$contato['whats']}");
        }

        Log::info('IniciarPesquisa.php - Todas as pesquisas foram encaminhadas.');

    }

}

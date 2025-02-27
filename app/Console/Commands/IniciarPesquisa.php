<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\PesquisaSatisfacaoJob;
use Illuminate\Support\Facades\Log;

use App\Models\TelefonePesquisa;
use App\Models\RespostaPesquisa;

class IniciarPesquisa extends Command
{
    protected $signature = 'app:iniciar_pesquisa'; //essa pesquisa usa o generate do ollama
    protected $description = 'Busca telefones dos usuários para a pesquisa e cria um job para cada um.';

    public function handle()
    {
        while (true) {
            // TELEFONE DE TESTE, INSERIR NO DB
            // $telefoneTeste = '4136141593';
            $telefoneTeste = '4184191656';
            if (!TelefonePesquisa::where('whats', $telefoneTeste)->exists()) {
                TelefonePesquisa::create(['whats' => $telefoneTeste]);
            }

            $contatos = TelefonePesquisa::all();

            // Nenhum telefone para pesquisa encontrado
            if ($contatos->isEmpty()) {
                sleep(10);
                return;
            }

            // Vai ciclar os telefones encontrados
            foreach ($contatos as $contato) {
                $existe = RespostaPesquisa::where('numeroWhats', $contato['whats'])
                    ->where('pesquisaConcluida', false)
                    ->first();

                if (!$existe) {
                    RespostaPesquisa::create(['numeroWhats' => $contato['whats']]);
                }

                dispatch(new PesquisaSatisfacaoJob($contato['whats']));
                Log::info("IniciarPesquisa.php - JOB criado para: {$contato['whats']}");
            }
            sleep(10);
        }
    }
}

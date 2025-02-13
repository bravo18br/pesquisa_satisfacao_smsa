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
        $telefoneTeste = '41984191656';
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
            $numeroWhats = $this->formatarNumeroWhatsApp($contato['whats']);
            $existe = ProcessadaPesquisa::where('numeroWhats', $numeroWhats)
                ->where('pesquisaConcluida', false)
                ->first();
            
            if (!$existe) {
                ProcessadaPesquisa::create(['numeroWhats' => $numeroWhats]);
            }
            
            dispatch(new PesquisaSatisfacaoJob($numeroWhats));
            Log::info("IniciarPesquisa.php - JOB criado para: {$numeroWhats}");
        }

        Log::info('IniciarPesquisa.php - Todas as pesquisas foram encaminhadas.');

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

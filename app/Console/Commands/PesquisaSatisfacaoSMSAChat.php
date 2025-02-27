<?php

namespace App\Console\Commands;

use App\Jobs\PesquisaSatisfacaoSMSAJob;
use App\Models\RespostaPesquisa;
use Illuminate\Console\Command;

class PesquisaSatisfacaoSMSAChat extends Command
{
    protected $signature = 'pesquisa:smsa';
    protected $description = 'Cria um robô para executar uma pesquisa de satisfação da SMSA';

    public function handle()
    {

        // Vai receber a lista de telefones para pesquisa pelo IPM
        $telefones = ['4184191656'];

        // Vai ciclar os telefones encontrados
        foreach ($telefones as $telefone) {
            $existe = RespostaPesquisa::where('numeroWhats', $telefone)
                ->where('pesquisaConcluida', false)
                ->first();

            if (!$existe) {
                RespostaPesquisa::create(['numeroWhats' => $telefone]);
            }
            dispatch(new PesquisaSatisfacaoSMSAJob($telefone));
        }
        sleep(10);

    }
}

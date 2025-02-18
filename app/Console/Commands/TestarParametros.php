<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BotsController;

class TestarParametros extends Command
{
    protected $signature = 'app:testar_ia';
    protected $description = 'Chama a função testarParametrosIA($prompt, $bot_nome)  para testar parâmetros da IA.';

    public function handle()
    {
        $contexto = '///CONTEXTO:Você pode falar livremente sobre qualquer assunto. Você perguntou para o usuário o nome do médico e se ele gostou do atendimento médico.';
        $mensagem = '///MENSAGEM:fui atendido pelo dr paulo. ele parece educado, mas ele estava pelado. achei estranho.';
        $formatoresposta = '///RESPOSTA IDEAL:Usando a mensagem do usuário como parâmetro, informe se o usuário gostou ou não do médico que lhe atendeu. Resposta curta e objetiva.';
        $model = 'llama3.2';

        $botsController = new BotsController;
        $botsController->testarParametrosIA($contexto . $mensagem . $formatoresposta, $model);
    }
}

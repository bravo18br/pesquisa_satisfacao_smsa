<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BotsController;

class TestarParametros extends Command
{
    protected $signature = 'app:testar_ia';
    protected $description = 'Chama a função testarParametrosIA($prompt, $model) para testar parâmetros da IA.';

    public function handle()
    {
        $contexto = '<|start_contexto|>Você é uma IA que executa pesquisa de satisafação sobre a qualidade do atendimento médico para a Secretaria Municipal de Saude da Prefeitura de Araucária.<|end_contexto|>';
        $mensagem = '<|start_mensagem_usuario|><|end_mensagem_usuario|>';
        $prompt = '<|start_prompt|>Envie mensagem inicial para um cidadão, informe que a pesquisa é sigilosa e pergunte se ele autoriza ("sim" ou "não") iniciar a pesquisa, conforme LGPD exige. Mensagem breve e objetiva. Seu único objetivo nessa mensagem é saber se o usuário autoriza a pesquisa.<|end_prompt|>';
        $model = 'llama3.2';
        $max_length=100;

        $botsController = new BotsController;
        $botsController->testarParametrosIA($contexto . $mensagem . $prompt, $model, $max_length);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\OllamaController;
use App\Models\Bot;

class BotsController extends Controller
{
    public function promptBot($mensagem, $bot_nome)
    {
        $bot = Bot::where("nome", $bot_nome)->first();
        $prompt = $bot->contexto . '<|start_mensagem_usuario|>' . $mensagem . '<|end_mensagem_usuario|>' . $bot->prompt;
        $params = [
            "model" => $bot->model,
            "prompt" => $prompt,
            "stream" => $bot->stream,
            "max_length" => $bot->max_length,
            "options" => [
                "temperature" => $bot->temperature,
                "top_p" => $bot->top_p,
            ]
        ];
        $ollama = new OllamaController();
        return $ollama->promptOllama($params);
    }

    public function testarParametrosIA($prompt, $model, $max_length)
    {
        for ($temperature = 0.0; $temperature <= 1.0; $temperature += 0.1) {
            for ($top_p = 0.0; $top_p <= 1.0; $top_p += 0.1) {
                $temperature = round($temperature, 1);
                $top_p = round($top_p, 1);

                $params = [
                    "model" => $model,
                    "prompt" => $prompt,
                    "stream" => false,
                    "max_length" => $max_length,
                    "options" => [
                        "temperature" => $temperature,
                        "top_p" => $top_p
                    ]
                ];
                $ollama = new OllamaController();
                $resposta = $ollama->promptOllama($params);

                $respostaData = $resposta->getData(true);
                $response = $respostaData['response'] ?? null;
                $message = sprintf(
                    "Model: %s Temperature: %.1f, Top_p: %.1f, Resposta: %s",
                    $model,
                    $temperature,
                    $top_p,
                    $response
                );

                echo $message . PHP_EOL;

            }
        }
    }

}
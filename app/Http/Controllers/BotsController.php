<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\OllamaController;
use App\Models\Bot;

class BotsController extends Controller
{
    public function promptBot($prompt, $bot_nome)
    {
        $bot = Bot::where("nome", $bot_nome)->first();
        $prompt = $bot->contexto . '///MENSAGEM:' . $prompt . $bot->formato_resposta;
        $params = [
            "model" => $bot->model,
            "prompt" => $prompt,
            "stream" => $bot->stream,
            "max_length" => $bot->max_length,
            "options" => [
                "temperature" => $bot->temperatura,
                "top_p" => $bot->top_p
            ]
        ];
        $ollama = new OllamaController();
        return $ollama->promptOllama($params);
    }

    public function testarParametrosIA($prompt, $model)
    {
        for ($temperature = 0.0; $temperature <= 1.0; $temperature += 0.1) {
            for ($top_p = 0.0; $top_p <= 1.0; $top_p += 0.1) {
                $temperature = round($temperature, 1);
                $top_p = round($top_p, 1);

                $this->promptEspecialBot($prompt, $temperature, $top_p, $model);

            }
        }
    }

    public function promptEspecialBot($prompt, $temperatura, $top_p, $model)
    {
        $params = [
            "model" => $model,
            "prompt" => $prompt,
            "stream" => false,
            "max_length" => 200,
            "options" => [
                "temperature" => $temperatura,
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
            $temperatura,
            $top_p,
            $response
        );
        Log::info($message);
        echo $message . PHP_EOL;


        return;
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\OllamaController;
use App\Models\Bot;

class BotsController extends Controller
{
    public function promptBot($prompt, $bot_nome)
    {
        // Log::info("promptBot($prompt, $bot_nome)");
        $bot = Bot::where("nome", $bot_nome)->first();
        $params = [
            "model" => $bot->model,
            "prompt" => $bot->contexto .' Prompt: <'.$prompt.'> '. $bot->formato_resposta,
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

    // public function testarParametros($phone, $prompt, $pushName) 
    // {
    //     $resultados = [];
    
    //     for ($temperature = 0.0; $temperature <= 1.0; $temperature += 0.1) {
    //         for ($top_p = 0.0; $top_p <= 1.0; $top_p += 0.1) {
    //             $temperature = round($temperature, 1);
    //             $top_p = round($top_p, 1);
    
    //             $resposta = $this->botAnaliseDeSentimento($phone, $prompt, $pushName, $temperature, $top_p);
    
    //             $resultados[] = [
    //                 'temperature' => $temperature,
    //                 'top_p' => $top_p,
    //                 'resposta' => $resposta
    //             ];
    
    //             // Opcional: Log para acompanhar os testes em tempo real
    //             Log::info("Teste - Temperature: $temperature, Top_p: $top_p, Resposta: $resposta");
    //         }
    //     }
    
    //     return $resultados;
    // }
    
}
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class OllamaController extends Controller
{
    public function promptOllama($params)
    {
        set_time_limit(600);
        $baseUrl = env('OLLAMA_API_URL', 'http://localhost:11434');

        try {
            $response = Http::timeout(600)->post("$baseUrl/api/generate", $params);

            if ($response->successful()) {
                return response()->json(["response" => $response->json('response')]);
            } else {
                return response()->json(["error" => $response->json('error')]);
            }
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function promptOllamaStream($params, callable $callback)
    {
        set_time_limit(600);
        $baseUrl = env('OLLAMA_API_URL', 'http://localhost:11434');

        try {
            $response = Http::timeout(600)->post("$baseUrl/api/generate",  $params);

            $body = $response->getBody();
            while (!$body->eof()) {
                $chunk = $body->read(1024); // Lendo 1024 bytes por vez
                $callback($chunk);
            }
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }




}
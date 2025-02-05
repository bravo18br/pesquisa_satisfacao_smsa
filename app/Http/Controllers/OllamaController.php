<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaController extends Controller
{
    public function promptOllama($params)
    {
        set_time_limit(180);
        $baseUrl = env('OLLAMA_API_URL', 'http://localhost:11434');

        try {
            $response = Http::timeout(120)->post("$baseUrl/api/generate", $params);

            if ($response->successful()) {
                return response()->json(["response" => $response->json('response')]);
            } else {
                return response()->json(["error" => $response->json('error')]);
            }
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
}
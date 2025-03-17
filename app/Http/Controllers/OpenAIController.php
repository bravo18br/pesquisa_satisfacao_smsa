<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIController extends Controller
{
    public function promptOpenAI($params)
    {
        set_time_limit(0);
        $baseUrl = env('OPENAI_API_URL', 'https://api.openai.com/v1');
        $apiKey = env('OPENAI_API_KEY');

        try {
            $response = Http::withToken($apiKey)
                ->timeout(1200)
                ->post("$baseUrl/completions", $params);

            Log::info("promptOpenAI response: " . $response);

            if ($response->successful()) {
                return response()->json(["response" => $response->json('choices')[0]['text'] ?? '']);
            } else {
                Log::error("promptOpenAI error: " . $response);
                return response()->json(["error" => $response->json('error')]);
            }
        } catch (\Exception $e) {
            Log::error("promptOpenAI catch: " . $e->getMessage());
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function chatOpenAI($params)
    {
        set_time_limit(0);
        $baseUrl = env('OPENAI_API_URL', 'https://api.openai.com/v1');
        $apiKey = env('OPENAI_API_KEY');

        try {
            $response = Http::withToken($apiKey)
                ->timeout(1200)
                ->post("$baseUrl/chat/completions", $params);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error("chatOpenAI error: " . $response);
                return response()->json(["error" => $response->json('error')]);
            }
        } catch (\Exception $e) {
            Log::error("chatOpenAI catch: " . $e->getMessage());
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function chatOpenAIStream($params)
    {
        set_time_limit(0);
        $baseUrl = env('OPENAI_API_URL', 'https://api.openai.com/v1');
        $apiKey = env('OPENAI_API_KEY');

        try {
            $response = Http::withToken($apiKey)
                ->timeout(1200)
                ->withOptions(['stream' => true])
                ->post("$baseUrl/chat/completions", $params);

            if (!$response->successful()) {
                Log::error("chatOpenAIStream error: " . $response->body());
                return response()->json(["error" => $response->json('error')], 500);
            }

            return response()->stream(function () use ($response) {
                $stream = $response->getBody()->detach();
                while (!feof($stream)) {
                    $chunk = fread($stream, 4096);
                    if ($chunk !== false) {
                        echo $chunk;
                        ob_flush();
                        flush();
                    }
                }
                fclose($stream);
            }, 200, [
                'Content-Type' => 'application/json',
                'X-Accel-Buffering' => 'no',
                'Cache-Control' => 'no-cache, must-revalidate',
                'Connection' => 'keep-alive',
            ]);
        } catch (\Exception $e) {
            Log::error("chatOpenAIStream catch: " . $e->getMessage());
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
}

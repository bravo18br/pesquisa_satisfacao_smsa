<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingController extends Controller
{
    public function generateEmbedding($chunk)
    {
        $url = env('OLLAMA_API_URL') . '/api/embeddings';

        try {
            // Log::info('📡 Enviando requisição para API de embeddings...');
            $response = Http::post($url, [
                'model' => 'nomic-embed-text',
                'prompt' => $chunk
            ]);

            if ($response->successful()) {
                // Log::info('✅ Embedding gerado com sucesso.');
                return $response->json();
            } else {
                Log::error('Erro na API de embeddings: ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Erro na requisição para API de embeddings: ' . $e->getMessage());
            return null;
        }
    }
}

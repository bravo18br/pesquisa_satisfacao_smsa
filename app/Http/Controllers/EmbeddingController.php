<?php

namespace App\Http\Controllers;

use App\Models\Embedding;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Pgvector\Laravel\Vector;

class EmbeddingController extends Controller
{
    public function createEmbeddings()
    {
        Log::info('📄 Iniciando a criação de embeddings.');

        $pdfPath = storage_path('app/DocumentoModeloRAG.pdf');

        // Verifica se o arquivo existe
        if (!file_exists($pdfPath)) {
            Log::error('❌ Arquivo PDF não encontrado: ' . $pdfPath);
            return 'Arquivo PDF não encontrado!';
        }

        // Obtém o texto do PDF usando o PDFController
        try {
            Log::info('🔍 Lendo o PDF...');
            $pdfController = app(PDFController::class);
            $text = $pdfController->lerPDF($pdfPath);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao ler o PDF: ' . $e->getMessage());
            return 'Erro ao ler o PDF.';
        }

        // Verifica se o texto foi extraído
        if (empty(trim($text))) {
            Log::warning('⚠ Nenhum texto encontrado no PDF.');
            return 'Nenhum texto encontrado no PDF.';
        }

        Log::info('📏 Texto extraído. Iniciando divisão em chunks...');
        $chunks = $this->chunkText($text, 200, 40);
        Log::info('🔢 Quantidade de chunks gerados: ' . count($chunks));

        if (empty($chunks)) {
            Log::warning('⚠ Nenhum chunk gerado!');
            return 'Nenhum chunk gerado!';
        }

        foreach ($chunks as $chunk) {
            // Log::info('🧠 Gerando embedding para um chunk...');

            try {
                $embeddingData = $this->generateEmbedding($chunk);

                // dd($embeddingData);

                $embedding = $embeddingData['embedding'] ?? null;

                if ($embedding) {
                    // Log::info('💾 Salvando embedding no banco de dados...');
                    Embedding::create([
                        'content' => $chunk,
                        'embedding' => new Vector($embedding), // Criar um objeto Vector corretamente
                    ]);

                    // Log::info('✅ Embedding salvo com sucesso!');
                } else {
                    Log::warning('⚠ Embedding retornou vazio.');
                }
            } catch (\Exception $e) {
                Log::error('❌ Erro ao salvar embedding: ' . $e->getMessage());
            }
        }

        Log::info('🎉 Embeddings criados com sucesso!');
        return 'Embeddings criados com sucesso!';
    }

    private function chunkText($text, $chunkSize, $overlap)
    {
        $chunks = [];
        $length = Str::length($text);

        for ($i = 0; $i < $length; $i += ($chunkSize - $overlap)) {
            $chunks[] = Str::substr($text, $i, $chunkSize);
        }

        return $chunks;
    }

    private function generateEmbedding($text)
    {
        $url = env('OLLAMA_API_URL') . '/api/embeddings';

        try {
            Log::info('📡 Enviando requisição para API de embeddings...');
            $response = Http::post($url, [
                'model' => 'nomic-embed-text',
                'prompt' => $text
            ]);

            if ($response->successful()) {
                Log::info('✅ Embedding gerado com sucesso.');
                return $response->json();
            } else {
                Log::error('❌ Erro na API de embeddings: ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error('❌ Erro na requisição para API de embeddings: ' . $e->getMessage());
            return null;
        }
    }
}

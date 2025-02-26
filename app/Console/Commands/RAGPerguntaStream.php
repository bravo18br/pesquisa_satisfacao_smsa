<?php

namespace App\Console\Commands;

use App\Http\Controllers\EmbeddingController;
use App\Http\Controllers\OllamaController;
use App\Models\Embedding;
use App\Models\FileMetadata;
use Illuminate\Console\Command;
use Pgvector\Vector;

class RAGPerguntaStream extends Command
{
    protected $signature = 'stream:pergunta {pergunta?}'; // não funciona
    protected $description = 'Faz uma pergunta usando RAG com resposta via streaming';

    public function handle()
    {
        // Capturar a pergunta do argumento ou solicitar ao usuário
        $perguntaTeste = $this->argument('pergunta') ?? $this->ask('Digite sua pergunta:');

        // Gerar embeddings
        $embeddingController = app(EmbeddingController::class);
        $embedding = new Vector($embeddingController->generateEmbedding($perguntaTeste)['embedding']);
        $contextEmbeddings = Embedding::orderByRaw('embedding <=> ?', [$embedding])->limit(3)->get();

        // Criar um contexto formatado para o Ollama
        $contexto = '';
        foreach ($contextEmbeddings as $index => $context) {
            $metadados = FileMetadata::where('id', $context->file_id)->first();
            $id = $index + 1;
            if ($metadados) {
                $contexto .= "<|start_context_{$id}|>\n";
                $contexto .= "<|start_context_filename_{$id}|>{$metadados->filename}<|end_context_filename_{$id}|>\n";
                $contexto .= "<|start_context_title_{$id}|>{$metadados->title}<|end_context_title_{$id}|>\n";
                $contexto .= "<|start_context_author_{$id}|>{$metadados->author}<|end_context_author_{$id}|>\n";
                $contexto .= "<|start_context_created_at_{$id}|>{$metadados->created_at}<|end_context_created_at_{$id}|>\n";
                $contexto .= "<|start_context_updated_at_{$id}|>{$metadados->updated_at}<|end_context_updated_at_{$id}|>\n";
                $contexto .= "<|start_context_content_{$id}|>{$context->content}<|end_context_content_{$id}|>\n";
                $contexto .= "<|end_context_{$id}|>\n\n";
            }
        }

        // Criando o prompt final para o Ollama
        $prompt = $contexto . "<|start_mensagem_usuario|>{$perguntaTeste}<|end_mensagem_usuario|>";

        // Configuração do request para streaming
        $params = [
            "model" => 'llama3.2',
            "prompt" => $prompt,
            "stream" => true,
            "max_length" => 100,
            "options" => [
                "temperature" => 0.0,
                "top_p" => 0.3,
            ]
        ];

        // Processamento da resposta via streaming
        $this->info("Gerando resposta...\n");
        $ollama = new OllamaController();
        $responseText = '';
        $ollama->promptOllamaStream($params, function ($chunk) use (&$responseText) {
            $decodedChunk = json_decode($chunk, true);
            $responseText .= $decodedChunk['response'];
        });

        // $ollama->promptOllamaStream($params, function ($chunk) use (&$responseText) {
        //     // Concatena todos os chunks recebidos
        //     $responseText .= $chunk;

        //     // Tenta processar cada linha como JSON válido
        //     $lines = explode("\n", $responseText);
        //     $cleanedResponse = '';

        //     foreach ($lines as $line) {
        //         $decodedChunk = json_decode($line, true);

        //         if (json_last_error() === JSON_ERROR_NONE && isset($decodedChunk['response'])) {
        //             $cleanedResponse .= $decodedChunk['response']; // Concatenar resposta válida
        //             echo $decodedChunk['response']; // Exibir progressivamente
        //             flush();
        //         }
        //     }
        // });

        // Exibir a resposta final completa
        // $this->info("\n\nResposta completa:");
        $this->info($responseText);

        $this->info("\nFinalizado!");
    }
}

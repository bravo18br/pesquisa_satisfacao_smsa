<?php

namespace App\Console\Commands;

use App\Http\Controllers\EmbeddingController;
use App\Http\Controllers\OllamaController;
use App\Models\Embedding;
use App\Models\FileMetadata;
use Illuminate\Console\Command;
use Pgvector\Vector;

class RAGPergunta extends Command
{
    protected $signature = 'block:pergunta {pergunta?}'; // Funcionando corretamente
    protected $description = 'Faz uma pergunta usando RAG';

    public function handle()
    {
        // Capturar a pergunta do argumento ou solicitar ao usuário
        $perguntaTeste = $this->argument('pergunta') ?? $this->ask('Digite sua pergunta:');

        // Gerar embeddings
        $embeddingController = app(EmbeddingController::class);
        $embedding = new Vector($embeddingController->generateEmbedding($perguntaTeste)['embedding']);
        $contextEmbeddings = Embedding::orderByRaw('embedding <=> ?', [$embedding])->limit(10)->get();

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
            "stream" => false,
            "max_length" => 300,
            "options" => [
                "temperature" => 0.0,
                "top_p" => 0.3,
            ]
        ];
        $ollama = new OllamaController();
        $response = $ollama->promptOllama($params);
        $responseData = json_decode($response->getContent(), true);
        $this->info($responseData['response']);
    }
}

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
        $pergunta = $this->argument('pergunta') ?? $this->ask('Digite sua pergunta:');
        // echo "Pergunta: $pergunta\n";


        
        // Gerar embeddings
        $inicioEmbeddings = microtime(true);
        $embeddingController = app(EmbeddingController::class);
        $embedding = new Vector($embeddingController->generateEmbedding($pergunta)['embedding']);
        $contextEmbeddings = Embedding::orderByRaw('embedding <=> ?', [$embedding])->limit(3)->get();
        $tempoEmbeddings = microtime(true) - $inicioEmbeddings;

        // Criar um contexto formatado para o Ollama
        $inicioProcessamento = microtime(true);
        $contexto = '';
        foreach ($contextEmbeddings as $index => $context) {
            $metadados = FileMetadata::where('id', $context->file_id)->first();
            $id = $index + 1;
            if ($metadados) {
                $contexto .= "<|start_context_{$id}|>\n";
                $contexto .= "<|start_context_metadata_nome_do_arquivo|>{$metadados->filename}<|end_context_metadata_nome_do_arquivo|>\n";
                $contexto .= "<|start_context_metadata_titulo|>{$metadados->title}<|end_context_metadata_titulo|>\n";
                $contexto .= "<|start_context_metadata_autor|>{$metadados->author}<|end_context_metadata_autor|>\n";
                $contexto .= "<|start_context_metadata_criado_em|>{$metadados->created_at}<|end_context_metadata_criado_em|>\n";
                $contexto .= "<|start_context_metadata_atualizado_em|>{$metadados->updated_at}<|end_context_metadata_atualizado_em|>\n";
                $contexto .= "<|start_context_conteudo|>{$context->content}<|end_context_conteudo|>\n";
                $contexto .= "<|end_context_{$id}|>";
            }
        }

        // Criando o prompt final para o Ollama
        $prompt = $contexto . "<|start_prompt|>{$pergunta}<|end_prompt|>";

        // Configuração do request para streaming
        $model = "llama3.2";
        $params = [
            "model" => $model,
            // "raw"=> true,
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
        $tempoProcessamento = microtime(true) - $inicioProcessamento;

        $this->warn("\n\nTelemetria:");
        $this->warn("Tempo embeddings: " . round($tempoEmbeddings, 4) . " segundos");
        $this->warn("Tempo {$model}: " . round($tempoProcessamento, 4) . " segundos");
    }
}

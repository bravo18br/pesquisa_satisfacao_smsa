<?php

namespace App\Console\Commands;

use App\Http\Controllers\EmbeddingController;
use App\Http\Controllers\OllamaController;
use App\Models\Embedding;
use App\Models\FileMetadata;
use Illuminate\Console\Command;
use Pgvector\Vector;

class RAGChat extends Command
{
    protected $signature = 'block:chat {mensagem?}'; //funcionando ok
    protected $description = 'Faz uma chat usando RAG';

    public function handle()
    {
        // Capturar a mensagem do argumento ou solicitar ao usuário
        $mensagem = $this->argument('mensagem') ?? $this->ask('Digite sua mensagem:');

        // Gerar embeddings
        $embeddingController = app(EmbeddingController::class);
        $embedding = new Vector($embeddingController->generateEmbedding($mensagem)['embedding']);
        $contextEmbeddings = Embedding::orderByRaw('embedding <=> ?', [$embedding])->limit(3)->get();

        // Criar um contexto formatado para o Ollama
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
        $prompt = $contexto . "<|start_prompt|>{$mensagem}<|end_prompt|>";

        // echo $prompt;

        // Configuração do request para streaming
        $params = [
            "model" => 'llama3.1',
            "messages" => [
                [
                    "role" => "system",
                    "content" => "Você é um assistente administrativo virtual. Forneça as informações solicitadas."
                ],
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ],
            "stream" => false,
            "max_length" => 300,
            "options" => [
                "temperature" => 0.0,
                "top_p" => 0.3,
            ]
        ];
        $ollama = new OllamaController();
        $response = $ollama->chatOllama($params);
        if (isset($response['message']['content'])) {
            $this->info($response['message']['content']);
        } else {
            $this->error("Erro: Resposta inesperada do Ollama.");
            $this->info("Resposta completa: " . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}

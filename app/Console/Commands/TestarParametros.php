<?php

namespace App\Console\Commands;

use App\Models\TelemetriaLLama31;
use Illuminate\Console\Command;
use App\Http\Controllers\OllamaController;
use Pgvector\Vector;
use App\Http\Controllers\EmbeddingController;
use App\Models\Embedding;
use App\Models\FileMetadata;

class TestarParametros extends Command
{
    protected $signature = 'app:testar_ia';
    protected $description = 'Chama a função testarParametrosIA($prompt, $model) para testar parâmetros da IA.';

    public function handle()
    {
        // $modelos = ['llama3.2', 'llama3.1', 'deepseek-r1:8b', 'mistral', 'gemma', 'phi3', 'llama2-uncensored'];
        $model = 'llama3.1';

        foreach (range(1, 5) as $limitEmbeddings) {
            foreach (range(0, 10) as $t) {
                $temperature = $t / 10;
                foreach (range(0, 10) as $p) {
                    $top_p = $p / 10;
                    $this->testarBlockGenerate($limitEmbeddings, $temperature, $top_p, $model);
                }
            }
        }
    }

    public function testarBlockGenerate($limitEmbeddings, $temperature, $top_p, $model)
    {
        // Verificar se teste já foi executado (se já está no db)
        $teste = TelemetriaLLama31::where('embeddings', $limitEmbeddings)
            ->where('temperature', $temperature)
            ->where('topP', $top_p)
            ->first();

        if ($teste) {
            return;
        }
        $this->info("\nTestando -> Modelo: $model | Embeddings: $limitEmbeddings | Temperature: $temperature | Top P: $top_p");

        $pergunta = 'faça um resumo do aviso de licitacao 104/2021';

        // Gerar embeddings
        $inicioEmbeddings = microtime(true);
        $embeddingController = app(EmbeddingController::class);
        $embedding = new Vector($embeddingController->generateEmbedding($pergunta)['embedding']);
        $contextEmbeddings = Embedding::orderByRaw('embedding <=> ?', [$embedding])->limit($limitEmbeddings)->get();
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
        $params = [
            "model" => $model,
            // "raw"=> true,
            "prompt" => $prompt,
            "stream" => false,
            "max_length" => 300,
            "options" => [
                "temperature" => $temperature,
                "top_p" => $top_p,
            ]
        ];
        $ollama = new OllamaController();
        $response = $ollama->promptOllama($params);
        $responseData = json_decode($response->getContent(), true);
        $tempoProcessamento = microtime(true) - $inicioProcessamento;

        // Se não houver resposta, pula a execução
        if (!isset($responseData['response']) || empty(trim($responseData['response']))) {
            $this->warn("Nenhuma resposta recebida do modelo. Descartando este teste.");
            return;
        }

        $teste = new TelemetriaLLama31();
        $teste->embeddings = $limitEmbeddings;
        $teste->temperature = $temperature;
        $teste->topP = $top_p;
        $teste->processamentoEmbeddings = round($tempoEmbeddings, 4);
        $teste->processamentoLLM = round($tempoProcessamento, 4);
        $teste->respostaLLM = $responseData['response'];
        $teste->save();
    }
}

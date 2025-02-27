<?php

namespace App\Console\Commands;

use App\Http\Controllers\EmbeddingController;
use App\Http\Controllers\PDFController;
use App\Models\Embedding;
use App\Models\FileMetadata;
use Illuminate\Console\Command;
use App\Http\Controllers\ChunkController;
use Smalot\PdfParser\Parser;
use Pgvector\Laravel\Vector;
use Illuminate\Support\Facades\Log;

class RAG extends Command
{
    protected $signature = 'app:rag'; // Funcionando corretamente
    protected $description = 'Testar funções de embedding.';

    public function handle()
    {
        // Caminho do PDF de exemplo
        $pdfPath = storage_path('app/DocumentoModeloRAG.pdf');
    
        // Verifica se o arquivo existe
        if (!file_exists($pdfPath)) {
            $this->error("\nArquivo PDF não encontrado: " . $pdfPath);
            return;
        }
    
        // Capturar metadados do PDF
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);
            $details = $pdf->getDetails();
    
            $filename = basename($pdfPath);
            $title = $details['Title'] ?? null;
            $author = $details['Author'] ?? null;
            $created_at = isset($details['CreationDate']) ? date('Y-m-d H:i:s', strtotime($details['CreationDate'])) : null;
    
            // 🔹 **Verifica se já existe um registro igual no banco**
            $existingFile = FileMetadata::where('filename', $filename)
                ->where('title', $title)
                ->where('author', $author)
                ->where('created_at', $created_at)
                ->first();
    
            if ($existingFile) {
                $this->warn("\nArquivo já foi inserido no pgvector. Processamento abortado.");
                return;
            }
    
            // 🔹 **Se não existir, cria um novo registro**
            $pdfMetadata = FileMetadata::create([
                'filename' => $filename,
                'title' => $title,
                'author' => $author,
                'created_at' => $created_at,
                'updated_at' => isset($details['ModDate']) ? date('Y-m-d H:i:s', strtotime($details['ModDate'])) : null,
                'source' => 'Local'
            ]);
    
        } catch (\Exception $e) {
            $this->error("\nErro ao capturar metadados: " . $e->getMessage());
            return;
        }
    
        $this->info("Iniciando leitura do PDF.");
        try {
            $pdfController = app(PDFController::class);
            $text = $pdfController->lerPDF($pdfPath);
            $this->info("PDF lido.");
        } catch (\Exception $e) {
            $this->error("\nException: " . $e->getMessage());
            return;
        }
    
        // Gerar os chunks
        $chunkController = app(ChunkController::class);
        $chunks = $chunkController->chunkText($text, 500, 100, $this);
    
        // Gerar embeddings
        $embeddingController = app(EmbeddingController::class);
        $this->withProgressBar($chunks, function ($chunk) use ($embeddingController, $pdfMetadata) {
            $embeddingData = $embeddingController->generateEmbedding($chunk);
            if ($embeddingData && isset($embeddingData['embedding'])) {
                Embedding::create([
                    'content' => $chunk,
                    'embedding' => new Vector($embeddingData['embedding']),
                    'file_id' => $pdfMetadata->id, // Relaciona com o arquivo processado
                ]);
            }
        });
    
        $this->info("\n\nProcesso concluído!");
    }
    
}

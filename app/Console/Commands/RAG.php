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

class RAG extends Command
{
    protected $signature = 'app:rag';
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

            $pdfMetadata = FileMetadata::create([
                'filename' => basename($pdfPath),
                'title' => $details['Title'] ?? null,
                'author' => $details['Author'] ?? null,
                'created_at' => isset($details['CreationDate']) ? date('Y-m-d H:i:s', strtotime($details['CreationDate'])) : null,
                'updated_at' => isset($details['ModDate']) ? date('Y-m-d H:i:s', strtotime($details['ModDate'])) : null,
                'source' => 'Local' // Pode mudar conforme necessário
            ]);

            $this->info("\nMetadados capturados");
        } catch (\Exception $e) {
            $this->error("\nErro ao capturar metadados: " . $e->getMessage());
            return;
        }

        // Obtém o texto do PDF usando o PDFController
        try {
            $pdfController = app(PDFController::class);
            $text = $pdfController->lerPDF($pdfPath);
            $this->info("\nPDF lido");
        } catch (\Exception $e) {
            $this->error("\nException: " . $e->getMessage());
            return;
        }

        // Gerar os chunks com barra de progresso
        $chunkController = app(ChunkController::class);
        $this->info("\nGerando chunks...");
        $chunks = $chunkController->chunkText($text, 500, 100, $this);

        // Gerar embeddings com barra de progresso
        $embeddingController = app(EmbeddingController::class);
        $this->info("\n\nGerando embeddings...");

        $this->withProgressBar($chunks, function ($chunk) use ($embeddingController, $pdfMetadata) {
            $embeddingData = $embeddingController->generateEmbedding($chunk);
            if ($embeddingData && isset($embeddingData['embedding'])) {
                Embedding::create([
                    'content' => $chunk,
                    'embedding' => new Vector($embeddingData['embedding']),
                    'file_id' => $pdfMetadata->id, // Amarra os embeddings aos metadados do PDF
                ]);
            }
        });

        $this->info("\n\nProcesso concluído!");
    }
}

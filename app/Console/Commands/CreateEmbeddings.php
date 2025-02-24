<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\EmbeddingController;

class CreateEmbeddings extends Command
{
    protected $signature = 'app:create_embeddings';
    protected $description = 'Testar funções de embedding.';

    public function handle()
    {
        $embController = new EmbeddingController();
        $retorno = $embController->createEmbeddings();
        echo $retorno;
    }
}

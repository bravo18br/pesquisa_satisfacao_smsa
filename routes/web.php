<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OllamaController;
use App\Http\Controllers\EmbeddingController;
use App\Http\Controllers\EvolutionController;

// Route::get('/', [EmbeddingController::class, 'createEmbeddings']);
// Route::get('/generate', [OllamaController::class, 'generate']);


Route::get('/evolution/testResposta', [EvolutionController::class, 'verificaMensagens']);
<?php

use App\Http\Controllers\EvolutionEventController;

Route::post('/webhook/evolution', [EvolutionEventController::class, 'store']);

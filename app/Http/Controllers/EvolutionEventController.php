<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EvolutionEvent;
use Illuminate\Support\Facades\Log;

class EvolutionEventController extends Controller
{
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $event = $data['event'];

            if ($event == "messages.upsert") {
                $message = strtolower($data['data']['message']['conversation'] ?? '');
                if(str_contains($message, 'ollama')) {
                    EvolutionEvent::create([
                        'event' => $data['event'] ?? 'unknown',
                        'instance' => $data['instance'] ?? 'unknown',
                        'data' => $data
                    ]);
                    // log::info('Evento salvo com sucesso!');
                    return response()->json(['message' => 'Evento salvo com sucesso!'], 201);
                }
            }
            return response()->json(['message' => 'Evento ignorado.'], 200);
        } catch (\Exception $e) {
            log::error('Evento não salvo. Erro: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao processar evento', 'details' => $e->getMessage()], 500);
        }
    }
}

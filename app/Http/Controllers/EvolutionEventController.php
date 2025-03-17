<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EvolutionEvent;
use Illuminate\Support\Facades\Log;

class EvolutionEventController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();

        // Verifica se o payload contém o evento "messages.upsert"
        if (empty($data) || !isset($data['event']) || $data['event'] !== 'messages.upsert') {
            return response()->json(['message' => 'Evento ignorado.'], 200);
        }

        try {
            // Aqui você pode também extrair e guardar somente os dados necessários
            EvolutionEvent::create([
                'data' => $data,
            ]);
            return response()->json(['message' => 'Evento salvo com sucesso!'], 201);
        } catch (\Exception $e) {
            Log::error('Evento não salvo. Erro: ' . $e->getMessage());
            Log::error('Data: ' . json_encode($data));
            return response()->json(['error' => 'Erro ao processar evento', 'details' => $e->getMessage()], 500);
        }
    }

}

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
            if ($data!=[]){
                EvolutionEvent::create([
                    'data' => $data
                ]); 
                return response()->json(['message' => 'Evento salvo com sucesso!'], 201);
            }else{
                return response()->json(['message' => 'Evento ignorado.'], 200);
            }
        } catch (\Exception $e) {
            log::error('Evento não salvo. Erro: ' . $e->getMessage());
            log::error('Data: ' . $data);
            return response()->json(['error' => 'Erro ao processar evento', 'details' => $e->getMessage()], 500);
        }
    }
}

// // Enviar mensagem de resposta
        // $payload = [
        //     'number' => $phone,
        //     'text' => $response,
        //     'delay' => 1,
        //     'linkPreview' => false,
        //     'mentionsEveryOne' => false
        // ];
    
        // $response = Http::withHeaders([
        //     'apikey' => $this->apiKey,
        //     'Content-Type' => 'application/json'
        // ])->post("{$this->apiUrl}/message/sendText/{$this->instance}", $payload);
    
        // if ($response->successful()) {
        //     // Aplica soft delete na mensagem
        //     // $mensagem->delete();
        //     return response()->json([
        //         'success' => true,
        //         'message' => 'Mensagem processada e resposta enviada!',
        //         'data' => $mensagem
        //     ]);
        // }
    
        // return response()->json([
        //     'success' => false,
        //     'error' => 'Erro ao enviar a resposta.',
        //     'details' => $response->json()
        // ], $response->status());
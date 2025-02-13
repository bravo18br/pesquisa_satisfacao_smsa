<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionController extends Controller
{
    private $apiUrl;
    private $apiKey;
    private $instance;

    public function __construct()
    {
        $this->apiUrl = env('EVOLUTION_API_URL');
        $this->apiKey = env('EVOLUTION_API_KEY');
        $this->instance = env('EVOLUTION_INSTANCE');
    } 
    
    public function enviaWhats($phone, $mensagem) {
        $payload = [
            'number' => $phone,
            'text' => $mensagem,
            'delay' => 1,
            'linkPreview' => false,
            'mentionsEveryOne' => false
        ];
    
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post("{$this->apiUrl}/message/sendText/{$this->instance}", $payload);
    
        if ($response->successful()) {
            log::info('EvolutionController.php - Mensagem enviada com sucesso');
            return true;
        }else{
            log::error('EvolutionController.php - Erro ao enviar mensagem: '. $response->body());
            return false;
        }
    }

    private function procuraCPF($mensagem) {
        // Expressão regular melhorada para capturar apenas CPFs válidos
        preg_match_all('/\b\d{3}\.?\d{3}\.?\d{3}-?\d{2}\b/', $mensagem, $matches);
    
        if (empty($matches[0])) {
            return null;
        }
    
        foreach ($matches[0] as $cpf) {
            // Remove pontos e traços para validação
            $cpfLimpo = preg_replace('/\D/', '', $cpf);
    
            // Se for válido, retorna formatado
            if ($this->validaCPF($cpfLimpo)) {
                return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $cpfLimpo);
            }
        }
    
        return null;
    }
    
    
    private function validaCPF($cpf) {
        // Elimina CPFs inválidos conhecidos
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
    
        // Calcula o primeiro dígito verificador
        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += $cpf[$i] * (($t + 1) - $i);
            }
            $digito = (($soma * 10) % 11) % 10;
            if ($cpf[$t] != $digito) {
                return false;
            }
        }
    
        return true;
    }
    
    private function limpaTelefone($telefone) {
        // Remove tudo que não for número
        $telefone = preg_replace('/\D/', '', $telefone);

        // Aplica a regex para formatar o número corretamente
        $telefone = preg_replace('/^55(\d{2})(\d{4,5})(\d{4})$/', '($1) $2-$3', $telefone);

        return $telefone;
    }
}

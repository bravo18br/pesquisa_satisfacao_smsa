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

    public function enviaWhats($phone, $mensagem)
    {
        $numero8digitos = $this->formatarNumero8digitos($phone);
        $response = $this->enviaMensagem($numero8digitos, $mensagem);

        if ($response->successful()) {
            return true;
        } else {
            $body = json_decode($response->body(), true);
            log::error('EvolutionController.php - Erro ao enviar mensagem: ' . $body);
            if (isset($body['response']['message'][0]['exists']) && $body['response']['message'][0]['exists'] === false) {
                $numero9digitos = $this->formatarNumero9digitos($phone);
                $response = $this->enviaMensagem($numero9digitos, $mensagem);
                if ($response->successful()) {
                    return true;
                } else {
                    return false;
                }
            }
            return false;
        }
    }

    private function enviaMensagem($phone, $mensagem)
    {
        $payload = [
            'number' => $phone,
            'text' => $mensagem,
            'delay' => 1,
            'linkPreview' => false,
            'mentionsEveryOne' => false
        ];

        return Http::withHeaders([
            'apikey' => $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post("{$this->apiUrl}/message/sendText/{$this->instance}", $payload);
    }

    private function formatarNumero8digitos(string $numero): string
    {
        $numero = preg_replace('/\D/', '', $numero); // Remove tudo que não for número
        $numero = ltrim($numero, '0'); // Remove zeros à esquerda

        if (strlen($numero) < 10 || strlen($numero) > 12) {
            throw new \InvalidArgumentException("Número inválido: $numero");
        }

        // Se o telefone tiver 9 dígitos, remove o primeiro (para ficar com 8)
        if (strlen($numero) === 11 || strlen($numero) === 12) {
            $ddd = substr($numero, 0, 2);
            $telefone = substr($numero, 3); // Remove o primeiro dígito do telefone (o "9")
        } else {
            $ddd = substr($numero, 0, 2);
            $telefone = substr($numero, 2);
        }

        return "55{$ddd}{$telefone}";
    }

    private function formatarNumero9digitos(string $numero): string
    {
        $numero = preg_replace('/\D/', '', $numero);
        $numero = ltrim($numero, '0');

        if (strlen($numero) < 10 || strlen($numero) > 12) {
            throw new \InvalidArgumentException("Número inválido: $numero");
        }

        // Se o telefone já tiver 9 dígitos, mantém
        if (strlen($numero) === 11 || strlen($numero) === 12) {
            $ddd = substr($numero, 0, 2);
            $telefone = substr($numero, 2);
        } else {
            // Se tiver apenas 8 dígitos, adiciona um "9" na frente
            $ddd = substr($numero, 0, 2);
            $telefone = '9' . substr($numero, 2);
        }

        return "55{$ddd}{$telefone}";
    }
}

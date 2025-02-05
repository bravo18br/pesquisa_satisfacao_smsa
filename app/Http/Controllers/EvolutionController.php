<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\EvolutionEvent;
use App\Http\Controllers\OllamaController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BotsController;

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
  
    public function verificaMensagens()
    {
        // Busca uma única mensagem contendo "AraucarIA"
        $mensagem = EvolutionEvent::whereRaw("data->'data'->'message'->>'conversation' LIKE ?", ['%Ollama%'])->first();
        // $mensagem = EvolutionEvent::first();
    
        if (!$mensagem) {
            return response()->json(['success' => false, 'message' => 'Nenhuma mensagem encontrada.']);
        }
    
        // Acesso direto ao campo JSON
        $data = $mensagem->data; // Não precisa de json_decode aqui
        
        // Obtém dados do remetente
        $phone = $data['data']['key']['remoteJid'];
        $prompt = $data['data']['message']['conversation'] ?? null;
        $pushName = $data['data']['pushName'] ?? null;
    
        // Enviar mensagem para o bot
        $bot = new BotsController();
        $bot->testarParametros($phone, $prompt, $pushName);
        // $resumo = $bot->botResumeProblema($phone, $prompt, $pushName);
        // $secretaria = $bot->botIdentificaSecretaria($phone, $prompt, $pushName);
        // $sentimento = $bot->botAnaliseDeSentimento($phone, $prompt, $pushName);
        // $tipoMensagem = $bot->botAnaliseTipoMensagem($phone, $prompt, $pushName);
        // $endereco = $bot->botDetectaEndereco($phone, $prompt, $pushName);

        // $glpi = [
        //     'Usuario' => $pushName,
        //     'Telefone' => $this->limpaTelefone($phone),
        //     'CPF' => $this->procuraCPF($prompt) ?? 'Não encontrado',
        //     'Secretaria' => $secretaria,
        //     'Solicitacao' => $resumo,
        //     'Endereco' => $endereco,
        //     'Sentimento' => $sentimento,
        //     'Tipo de Mensagem' => $tipoMensagem
        // ];

        // if($this->enviaWhats($phone, $glpi)){
        //     // Aplica soft delete na mensagem
        //     $mensagem->delete();
        //     return response()->json($glpi);
        // } else{
        //     return response()->json(['success' => false, 'message' => 'Erro ao enviar mensagem.']);
        // }
    }  
    
    private function enviaWhats($phone, $mensagem) {
        $payload = [
            'number' => $phone,
            'text' => json_encode($mensagem),
            'delay' => 1,
            'linkPreview' => false,
            'mentionsEveryOne' => false
        ];
    
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post("{$this->apiUrl}/message/sendText/{$this->instance}", $payload);
    
        if ($response->successful()) {
            log::info('Mensagem enviada com sucesso');
            return true;
        }else{
            log::error('Erro ao enviar mensagem: '. $response->body());
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

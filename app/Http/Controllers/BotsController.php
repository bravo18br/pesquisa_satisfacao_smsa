<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\OllamaController;

class BotsController extends Controller
{
    public function promptBot1($phone, $prompt, $pushName, $temperature, $top_p)
    {
        // Esse é o bot de recepção de novos chamados. Atende o primeiro contato.

        $contexto = 'Você é Ollama, um assistente de IA da Prefeitura de Araucaria e está atendendo o cidadao '. $pushName;
        $pergunta = $prompt;
        $formatoResposta = ' Resposta gentil e curta, em pt-br.';

        $params = [
            "model" => "llama3.2",
            "prompt" => 'Contexto: '.$contexto.'. Leve em conta o contexto informado: '.$pergunta. $formatoResposta,
            "stream" => false,    
            // "max_length" => 100,     
            "options" => [
                "temperature" => $temperature ?? 0.5,
                "top_p" => $top_p ?? 0.5
            ]            
        ];

        $ollama = new OllamaController();
        $response = $ollama->promptOllama($params);
    }
    public function botResumeProblema($phone, $prompt, $pushName, $temperature, $top_p)
    {
        $contexto = 'Recebi essa mensagem de um usuário e preciso de um resumo do problema. Não informar endereço no resumo. Não informar documentos no resumo.';
        $problema = $prompt;
        $formatoResposta = 'Responda apenas uma frase, em pt-br. Comece com "O usuário solicita <resumo da mensagem>"';

        $params = [
            "model" => "llama3.2",
            "prompt" => $contexto.' Mensagem: <'.$problema.'> '. $formatoResposta,
            "stream" => false,    
            // "max_length" => 100,     
            "options" => [
                "temperature" => $temperature ?? 0.4,
                "top_p" => $top_p ?? 1
            ]            
        ];

        $ollama = new OllamaController();
        $response = $ollama->promptOllama($params);
        $responseData = $response->getData(true);
        return $responseData['response'];
    }

    public function botIdentificaSecretaria($phone, $prompt, $pushName, $temperature, $top_p)
    {
        $contexto = 'Você é assistente da prefeitura e deve classificar mensagens, de acordo com a secretaria responsavel. As secretarias disponíveis são: SMAD Administração, SMAG Agricultura, SMAS Assistência Social, SMCS Comunicação Social, Controladoria, SMCT Cultura e Turismo, SMED Educação, SMEL Esporte e Lazer, SMFI Finanças, SMGP Gestão de Pessoas, SMGO Governo, SMMA Meio Ambiente, SMOP Obras e Transporte, SMPL Planejamento, SMPP Políticas Públicas, PGM Procuradoria, SMSA Saúde, SMSP Segurança Pública, SMTE Trabalho e Emprego, SMUR Urbanismo, SMCIT Ciência Inovação e Tecnologia';
        $problema = $prompt;
        $formatoResposta = ' Responda apenas o nome da secretaria, sem ponto, nada mais, em pt-br.';

        $params = [
            "model" => "llama3.2",
            "prompt" => 'Contexto: '.$contexto.'. Mensagem: '.$problema. $formatoResposta,
            "stream" => false,    
            // "max_length" => 100,     
            "options" => [
                "temperature" => $temperature ?? 0.9,
                "top_p" => $top_p ?? 0.9
            ]            
        ];

        $ollama = new OllamaController();
        $response = $ollama->promptOllama($params);
        $responseData = $response->getData(true);
        return $responseData['response'];
    }

    public function botAnaliseDeSentimento($phone, $prompt, $pushName, $temperature, $top_p)
    {
        $contexto = 'Você deve analisar o sentimento do usuário que enviou a mensagem. As opções permitidas são: Satisfeito, Irritado, Triste, Entusiasmado ou Neutro';
        $mensagem = $prompt;
        $formatoResposta = ' Responda apenas o sentimento do usuário, sem ponto, nada mais, em pt-br.';

        $params = [
            "model" => "llama3.2",
            "prompt" => 'Contexto: '.$contexto.'. Mensagem: '.$mensagem. $formatoResposta,
            "stream" => false,    
            // "max_length" => 100,     
            "options" => [
                "temperature" => $temperature ?? 0.5,
                "top_p" => $top_p ?? 0.5
            ]            
        ];

        $ollama = new OllamaController();
        $response = $ollama->promptOllama($params);
        $responseData = $response->getData(true);
        return $responseData['response'];
    }

    public function botAnaliseTipoMensagem($phone, $prompt, $pushName, $temperature, $top_p)
    {
        $contexto = 'Você deve classificar a mensagem. As classificações permitidas são: Dúvida, Reclamação, Sugestão, Elogio ou Outro';
        $mensagem = $prompt;
        $formatoResposta = ' Responda apenas a classificação da mensagem, sem ponto, nada mais, em pt-br.';

        $params = [
            "model" => "llama3.2",
            "prompt" => 'Contexto: '.$contexto.'. Mensagem: '.$mensagem. $formatoResposta,
            "stream" => false,    
            // "max_length" => 100,     
            "options" => [
                "temperature" => $temperature ?? 0.5,
                "top_p" => $top_p ?? 0.5
            ]            
        ];

        $ollama = new OllamaController();
        $response = $ollama->promptOllama($params);
        $responseData = $response->getData(true);
        return $responseData['response'];
    }

    public function botDetectaEndereco($phone, $prompt, $pushName, $temperature, $top_p)
    {
        $contexto = 'A seguinte mensagem contém um relato de problema seguido de um endereço. Extraia apenas o endereço da mensagem, sem incluir informações adicionais';
        $mensagem = $prompt;
        $formatoResposta = ' Responda apenas o endereço encontrado. Caso não encontre, responda "Não encontrado", sem ponto, nada mais, em pt-br.';

        $params = [
            "model" => "llama3.2",
            "prompt" => $contexto.'. Mensagem: <'.$mensagem.'> '. $formatoResposta,
            "stream" => false,    
            // "max_length" => 100,     
            "options" => [
                "temperature" => $temperature ?? 0.5,
                "top_p" => $top_p ?? 0.5
            ]            
        ];

        $ollama = new OllamaController();
        $response = $ollama->promptOllama($params);
        $responseData = $response->getData(true);
        return $responseData['response'];
    }

    public function testarParametros($phone, $prompt, $pushName) 
    {
        $resultados = [];
    
        for ($temperature = 0.0; $temperature <= 1.0; $temperature += 0.1) {
            for ($top_p = 0.0; $top_p <= 1.0; $top_p += 0.1) {
                $temperature = round($temperature, 1);
                $top_p = round($top_p, 1);
    
                $resposta = $this->botAnaliseDeSentimento($phone, $prompt, $pushName, $temperature, $top_p);
    
                $resultados[] = [
                    'temperature' => $temperature,
                    'top_p' => $top_p,
                    'resposta' => $resposta
                ];
    
                // Opcional: Log para acompanhar os testes em tempo real
                Log::info("Teste - Temperature: $temperature, Top_p: $top_p, Resposta: $resposta");
            }
        }
    
        return $resultados;
    }
    
}
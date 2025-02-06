<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesquisa;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\EvolutionController;
use App\Http\Controllers\EvolutionEventController;

class PesquisaController extends Controller
{
    public function iniciaPesquisa()
    {
        $evolutionEvent = new EvolutionEventController();
        
        $contato = Pesquisa::whereNotNull('telefone')->first();
        $numero = $contato['telefone'];
        $numeroWhats = $this->formatarNumeroWhatsApp($numero);

        if ($contato){
            $mensagem = "Olá! Eu sou Carlos, quero saber a sua opinião sobre o seu atendimento médico em Araucaria hoje.\n"
            . "Eu sou uma inteligência artificial e suas respostas são totalmente anônimas, pode ficar tranquilo.\n"
            . "Usarei essas informações para melhorar os serviços da prefeitura na cidade.\n"
            . "Podemos iniciar a pesquisa? Responda \"sim\" ou \"não participar\"";
  
            $evolution = new EvolutionController();
            $evolution->enviaWhats($numeroWhats, $mensagem);
  



            // NÃO ESQUECER DE APAGAR O TELEFONE AO TÉRMINO DA PESQUISA (ANONIMIZAÇÃO) APAGAR TMB A GERAÇÃO DE LOGS
            return response()->json(['success' => true, 'message' => 'Pesquisa realizada.']);
        }else{
            log::info('Nenhuma pesquisa de satisfação pendente.');
            return response()->json(['success' => true, 'message' => 'Nenhuma pesquisa de satisfação pendente.']);
        }
    }

    private function formatarNumeroWhatsApp(string $numero): string
    {
        // Remover todos os caracteres não numéricos
        $numero = preg_replace('/\D/', '', $numero);
    
        // Remover o zero à esquerda, se houver
        $numero = ltrim($numero, '0');
    
        // Verificar se o número tem DDD + telefone de 8 ou 9 dígitos
        if (strlen($numero) === 10 || strlen($numero) === 11) {
            $ddd = substr($numero, 0, 2);
            $telefone = substr($numero, 2);
        } elseif (strlen($numero) === 11 || strlen($numero) === 12) {
            $ddd = substr($numero, 0, 3);
            $telefone = substr($numero, 3);
        } else {
            throw new InvalidArgumentException("Número inválido: $numero");
        }
    
        // Retorna no formato esperado pelo WhatsApp
        return "55{$ddd}{$telefone}@s.whatsapp.net";
    }
}

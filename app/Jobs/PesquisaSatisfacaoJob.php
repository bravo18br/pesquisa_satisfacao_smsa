<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\EvolutionController;
use App\Http\Controllers\BotsController;
use App\Models\Pesquisa;
use App\Models\EvolutionEvent;

class EnviarPesquisaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contato;

    public function __construct(Pesquisa $contato)
    {
        $this->contato = $contato;
    }

    public function handle()
    {
        if (!$this->contato) {
            return;
        }

        while ($this->contato->status_pesquisa != 'encerrada'){
            switch ($this->contato->status_pesquisa) {
                case 'job iniciado':
                    $this->jobIniciado();
                break;
                case 'primeiro contato':
                    $this->primeiroContato();
                break;
                case 'lgpd autorizado':
                    $this->segundoContato();
                break;    
                case 'unidade':
                    $this->terceiroContato();
                break;    
                case 'recepcao':
                    $this->quartoContato();
                break;    
                case 'recepcao':
                    $this->quartoContato();
                break;                                                          
            }
            sleep(60);
            $this->contato->refresh();
        }

    }

    private function jobIniciado()
    {
        // Enviar mensagem para o usuário
        $numero = $this->contato->telefone;
        $numeroWhats = $this->formatarNumeroWhatsApp($numero);
        $mensagem = "Olá! Eu sou Carlos, quero saber a sua opinião sobre o seu atendimento médico em Araucária hoje.\n"
            . "Eu sou uma inteligência artificial e suas respostas são totalmente anônimas.\n"
            . "Usarei essas informações apenas para melhorar os serviços de saúde na cidade.\n"
            . "Podemos iniciar a pesquisa? Responda \"sim\" ou \"não\"";

        $evolution = new EvolutionController();
        $status_envio = $evolution->enviaWhats($numeroWhats, $mensagem);

        if ($status_envio) {
            $this->contato->update(['status_pesquisa' => 'primeiro contato']);
        }
    }

    private function primeiroContato()
    {
        // NESSA FUNCTION, ELE VAI RECEBER A RESPOSTA SE O USUÁRIO ACEITOU OU NÃO PARTICIPAR DA PESQUISA
        // NA SEQUÊNCIA, VAI PERGUNTAR O NOME DA UNIDADE QUE ELE FOI ATENDIDO

        $numero = $this->contato->telefone;
        $numeroWhats = $this->formatarNumeroWhatsApp($numero);

        // Buscar todas as mensagens do usuário
        $mensagensQuery = EvolutionEvent::whereRaw("data->'data'->'key'->>'remoteJid' = ?", [$numeroWhats]);
        $mensagens = $mensagensQuery->pluck("data->'data'->'message'->>'conversation'");
        $historicoMensagens = $mensagens->implode("\n");
    
        // Apagar as mensagens do banco para evitar reprocessamento
        $mensagensQuery->delete();
    
        // Processar as mensagens
        $bot = new BotsController();
        $resposta = $bot->botPrimeiroContato($historicoMensagens);

        if ($resposta === 'sim'){
            $this->contato->update(['status_pesquisa' => 'lgpd autorizado']);
            $mensagem = "Que bom que aceitou participar!\n"
                . "Vou iniciar a primeira pergunta então:\n"
                . "Qual o nome da unidade de atendimento médico que você esteve hoje?";
    
            $evolution = new EvolutionController();
            $evolution->enviaWhats($numeroWhats, $mensagem);
        } else {
            $this->contato->update(['status_pesquisa' => 'encerrada']);
            $mensagem = "Está sem tempo para responder agora?\n"
                . "Sem problemas, deixamos para uma próxima oportunidade!\n"
                . "Agradeço pela atenção!";
    
            $evolution = new EvolutionController();
            $evolution->enviaWhats($numeroWhats, $mensagem);
        }
    }

    private function segundoContato()
    {
        $numero = $this->contato->telefone;
        $numeroWhats = $this->formatarNumeroWhatsApp($numero);

        // Buscar todas as mensagens do usuário
        $mensagensQuery = EvolutionEvent::whereRaw("data->'data'->'key'->>'remoteJid' = ?", [$numeroWhats]);
        $mensagens = $mensagensQuery->pluck("data->'data'->'message'->>'conversation'");
        $historicoMensagens = $mensagens->implode("\n");
    
        // Apagar as mensagens do banco para evitar reprocessamento
        $mensagensQuery->delete();

        // ESSE BOT PROCESSA A RESPOSTA SOBRE QUAL UNIDADE MÉDICA ELE FOI ATENDIDO
        $bot = new BotsController();
        $resposta = $bot->botSegundoContato($historicoMensagens);
        $this->contato->update(['unidade' => $resposta]);
        $this->contato->update(['status_pesquisa' => 'unidade']);

        // NA SEQUÊNCIA, VAI PERGUNTAR A OPINIÃO SOBRE A RECEPÇÃO DA UNIDADE
        $mensagem = "Ok, anotado.\n"
            . "Agora, o que você achou da recepção da unidade?\n"
            . "Você foi bem instruído ao chegar e ao sair do local?";

        $evolution = new EvolutionController();
        $evolution->enviaWhats($numeroWhats, $mensagem);        
    }

    private function terceiroContato()
    {
        $numero = $this->contato->telefone;
        $numeroWhats = $this->formatarNumeroWhatsApp($numero);

        // Buscar todas as mensagens do usuário
        $mensagensQuery = EvolutionEvent::whereRaw("data->'data'->'key'->>'remoteJid' = ?", [$numeroWhats]);
        $mensagens = $mensagensQuery->pluck("data->'data'->'message'->>'conversation'");
        $historicoMensagens = $mensagens->implode("\n");
    
        // Apagar as mensagens do banco para evitar reprocessamento
        $mensagensQuery->delete();

        // ESSE BOT RESPONDE SOBRE O QUE ELE ACHOU DA RECEPÇÃO DA UNIDADE
        $bot = new BotsController();
        $resposta = $bot->botTerceiroContato($historicoMensagens); 
        $this->contato->update(['atendimento_recepcao' => $resposta]);
        $this->contato->update(['status_pesquisa' => 'recepcao']);

        // NA SEQUÊNCIA, VAI PERGUNTAR A OPINIÃO SOBRE A CONSERVAÇÃO E LIMPEZA DA UNIDADE
        $mensagem = "Ok, já anotei aqui.\n"
            . "E sobre a limpeza e conservação do local? Os banheiros e corredores, estavam em ordem?";

        $evolution = new EvolutionController();
        $evolution->enviaWhats($numeroWhats, $mensagem);        
    }    

    private function quartoContato()
    {
        $numero = $this->contato->telefone;
        $numeroWhats = $this->formatarNumeroWhatsApp($numero);

        // Buscar todas as mensagens do usuário
        $mensagensQuery = EvolutionEvent::whereRaw("data->'data'->'key'->>'remoteJid' = ?", [$numeroWhats]);
        $mensagens = $mensagensQuery->pluck("data->'data'->'message'->>'conversation'");
        $historicoMensagens = $mensagens->implode("\n");
    
        // Apagar as mensagens do banco para evitar reprocessamento
        $mensagensQuery->delete();

        // ESSE BOT RESPONDE SOBRE A CONSERVAÇÃO E LIMPEZA DA UNIDADE
        $bot = new BotsController();
        $resposta = $bot->botQuartoContato($historicoMensagens); 
        $this->contato->update(['ambiente_limpeza' => $resposta]);
        $this->contato->update(['status_pesquisa' => 'limpeza']);

        // NA SEQUÊNCIA, VAI PERGUNTAR A OPINIÃO SOBRE A CONSERVAÇÃO E LIMPEZA DA UNIDADE
        $mensagem = "Ok, já anotei aqui.\n"
            . "E sobre a limpeza e conservação do local? Os banheiros e corredores, estavam em ordem?";

        $evolution = new EvolutionController();
        $evolution->enviaWhats($numeroWhats, $mensagem);        
    }   

    private function formatarNumeroWhatsApp(string $numero): string
    {
        $numero = preg_replace('/\D/', '', $numero);
        $numero = ltrim($numero, '0');

        if (strlen($numero) === 10 || strlen($numero) === 11) {
            $ddd = substr($numero, 0, 2);
            $telefone = substr($numero, 2);
        } elseif (strlen($numero) === 12) {
            $ddd = substr($numero, 0, 3);
            $telefone = substr($numero, 3);
        } else {
            throw new \InvalidArgumentException("Número inválido: $numero");
        }

        return "55{$ddd}{$telefone}@s.whatsapp.net";
    }
}

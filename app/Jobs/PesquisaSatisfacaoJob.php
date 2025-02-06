<?php

namespace App\Jobs;

use App\Models\Pesquisa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\EvolutionController;

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
                    // procurar resposta no db (DESENVOLVER)
                    // CONFERIR A ULTIMA ATUALIZAÇÃO, PARA SABER QUANTO TEMPO ESTÁ ESPERANDO
                    
                break;
            }
        }

        // // Finaliza a pesquisa
        // $this->contato->update([
        //     'telefone' => null,
        //     'status_pesquisa' => 'encerrada',
        // ]);
    }

    private function jobIniciado()
    {
        // Enviar mensagem para o usuário
        $numero = $this->contato->telefone;
        $numeroWhats = $this->formatarNumeroWhatsApp($numero);
        $mensagem = "Olá! Eu sou Carlos, quero saber a sua opinião sobre o seu atendimento médico em Araucária hoje.\n"
            . "Eu sou uma inteligência artificial e suas respostas são totalmente anônimas, pode ficar tranquilo.\n"
            . "Usarei essas informações apenas para melhorar os serviços de saúde na cidade.\n"
            . "Podemos iniciar a pesquisa? Responda \"sim\" ou \"não participar\"";

        $evolution = new EvolutionController();
        $status_envio = $evolution->enviaWhats($numeroWhats, $mensagem);

        if ($status_envio) {
            $this->contato->update(['status_pesquisa' => 'primeiro contato']);
        }
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

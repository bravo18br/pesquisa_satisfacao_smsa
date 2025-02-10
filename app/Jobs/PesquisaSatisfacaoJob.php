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

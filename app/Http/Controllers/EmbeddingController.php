<?php

namespace App\Http\Controllers;

use App\Models\Embedding;
use Illuminate\Support\Str;

class EmbeddingController extends Controller
{
    public function createEmbeddings()
    {
        // Texto de exemplo
        $text = "O suricata (Suricata suricatta), também conhecida como suricate ou suricato, é uma espécie de mamífero da família Herpestidae. É a única espécie descrita para o gênero Suricata.[2][3] Pode ser encontrada na África do Sul, Botsuana, Namíbia e Angola.[1] Estes animais têm cerca de meio metro de comprimento (incluindo a cauda), em média 730 gramas de peso, e pelagem acastanhada. Os suricatas alimentam-se de pequenos artrópodes, principalmente escaravelhos e aranhas. Têm garras afiadas nas patas, que lhes permitem escavar a superfície do chão e tem dentes afiados para penetrar nas carapaças quitinosas das suas presas. Outra característica distinta é a sua capacidade de se elevarem nas patas traseiras, utilizando a cauda como terceiro apoio. Etimologia O nome suricata vem do francês suricate, cuja etimologia é desconhecida, assume-se que venha de uma língua da África meridional. ricatas no Zoo de Auckland. ossuem listras paralelas em suas costas, que se estendem desde a base da cauda até os ombros. Os padrões de listras são únicos para cada suricata. Ecologia Os suricatas são exclusivamente diurnos e vivem em colónias de até 40 indivíduos, que constroem um complicado sistema de túneis no subsolo, onde permanecem durante a noite. Têm uma longevidade entre 5 a 12 anos, atingindo até aos 15 em cativeiro. Dentro do grupo, os animais revezam-se nas tarefas de vigia e proteção das crias da comunidade. O sistema social dos suricata é complexo e inclui uma linguagem própria que parece indicar, por exemplo, o tipo de um predador que se aproxima. Atingem a maturidade sexual com um ano de idade, podendo ter de três a cinco filhotes por ninhada. Podem ter até quatro ninhadas por ano. Se reproduzem em qualquer época do ano, mas a maioria dos nascimentos ocorrem nas estações mais quentes. Estudos mostram que os suricatas são capazes de ensinar ativamente suas crias a caçarem, um método semelhante à capacidade humana de ensinar. As suricata desenvolveram um modo específico de enfrentar cada predador, no caso de aves de rapina, escondem-se dentro das galerias, no caso de chacal ou hiena, irão tentar afugentá-lo com sombras e barulhos, no caso de cobra irão lutar com ela e até mesmo comê-la. Dieta. As suricatas são carnívoras e alimentam-se principalmente de pequenos artrópodes como larvas de escaravelho, e borboletas, mas também milípedes, aranhas, anfíbios, e aves pequenas. As crias de suricata com mais de 2 meses são ensinadas a caçar por professoras em escolas, ao fim de algumas semanas de treino, as suricatas já conseguem caçar presas como escorpiões e najas, que são as suas presas preferidas, aos quais estão imunizadas.";
        
        // Divisão em chunks com interseção (overlaping)
        $chunks = $this->chunkText($text, 100, 20);
        if (!$chunks){
            return response()->json(['message' => 'Chunk vazio!']);
        } else {
            foreach ($chunks as $chunk) {
                // Crie os embeddings (substitua pelo seu método de geração de embeddings)
                $embedding = $this->generateEmbedding($chunk);
    
                // Salve no banco de dados
                Embedding::create([
                    'content' => $chunk,
                    'embedding' => $embedding,
                ]);
            }
        }
        return response()->json(['message' => 'Embeddings criados com sucesso!']);
    }

    private function chunkText($text, $chunkSize, $overlap)
    {
        $chunks = [];
        $length = Str::length($text);

        for ($i = 0; $i < $length; $i += ($chunkSize - $overlap)) {
            $chunks[] = Str::substr($text, $i, $chunkSize);
            if ($i + $chunkSize >= $length) break;
        }

        return $chunks;
    }

    private function generateEmbedding($text)
    {
        $url = env('OLLAMA_API_URL').'/embed';
        
        $data = [
            'text' => $text,
        ];
        
        $options = [
            'http' => [
                'header'  => "Content-Type: application/json",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];
        
        $context  = stream_context_create($options);

    }
    
}

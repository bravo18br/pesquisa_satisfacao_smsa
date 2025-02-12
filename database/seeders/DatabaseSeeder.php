<?php

namespace Database\Seeders;

use App\Models\Bot;
use App\Models\PerguntaPesquisa;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Iniciando inserção de bots...');

        $bots = [
            [
                'nome' => 'lgpdAutorizacaoBOT',
                'contexto' => 'Você perguntou para o usuário se ele aceita ou não participar de uma pesquisa de satisfação.',
                'formato_resposta' => 'De acordo com a prompt do usuário, responda "sim" se ele aceitou participar da pesquisa, ou "não" caso não tenha aceitado.',
                'temperatura' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'unidadeAtendimentoBOT',
                'contexto' => 'Você perguntou para o usuário qual unidade de atendimento médico ele esteve hoje.',
                'formato_resposta' => 'De acordo com a prompt do usuário, qual o nome da unidade de atendimento médico?',
                'temperatura' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'recepcaoUnidadeBOT',
                'contexto' => 'Você perguntou para o usuário o que ele achou da recepção do local, se ele foi bem instruído ao chegar e ao sair da unidade.',
                'formato_resposta' => 'De acordo com a prompt do usuário, faça um breve resumo sobre a experiência dele, e informe se ele gostou ou não da recepção da unidade.',
                'temperatura' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'limpezaConservacaoBOT',
                'contexto' => 'Você perguntou para o usuário o que ele achou da limpeza e conservação do local, se os banheiros e corredores estavam em ordem.',
                'formato_resposta' => 'De acordo com a prompt do usuário, faça um breve resumo sobre a experiência dele, e informe se ele gostou ou não da limpeza e conservação da unidade.',
                'temperatura' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'detectaEnderecoBOT',
                'contexto' => 'Você perguntou o endereço do usuário.',
                'formato_resposta' => 'De acordo com a prompt do usuário, qual o endereço informado? Caso não encontre, responda "Não encontrado", sem ponto, nada mais, em pt-br.',
                'temperatura' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'classificaTipoMensagemBOT',
                'contexto' => 'Você deve classificar o prompt do usuário. As classificações permitidas são: Dúvida, Reclamação, Sugestão, Elogio ou Outro',
                'formato_resposta' => 'De acordo com a prompt do usuário, qual a classificação do prompt, sem ponto, nada mais, em pt-br.',
                'temperatura' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'classificaSentimentoBOT',
                'contexto' => 'Você deve analisar o prompt do usuário para identificar o sentimento dele. As opções permitidas são: Satisfeito, Irritado, Triste, Entusiasmado ou Neutro',
                'formato_resposta' => 'De acordo com a prompt do usuário, qual o sentimento do usuário, sem ponto, nada mais, em pt-br.',
                'temperatura' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'identificaSecretariaBOT',
                'contexto' => 'Você é assistente da prefeitura e deve classificar o prompt do usuário, de acordo com a secretaria responsavel. As secretarias disponíveis são:'
                    . ' SMAD Administração,'
                    . ' SMAG Agricultura,'
                    . ' SMAS Assistência Social,'
                    . ' SMCS Comunicação Social,'
                    . ' CNTR Controladoria,'
                    . ' SMCT Cultura e Turismo,'
                    . ' SMED Educação,'
                    . ' SMEL Esporte e Lazer,'
                    . ' SMFI Finanças,'
                    . ' SMGP Gestão de Pessoas,'
                    . ' SMGO Governo,'
                    . ' SMMA Meio Ambiente,'
                    . ' SMOP Obras e Transporte,'
                    . ' SMPL Planejamento,'
                    . ' SMPP Políticas Públicas,'
                    . ' PGM Procuradoria,'
                    . ' SMSA Saúde,'
                    . ' SMSP Segurança Pública,'
                    . ' SMTE Trabalho e Emprego,'
                    . ' SMUR Urbanismo,'
                    . ' SMCIT Ciência Inovação e Tecnologia',
                'formato_resposta' => 'De acordo com a prompt do usuário, indique o nome da secretaria responsável, sem ponto, nada mais, em pt-br.',
                'temperatura' => 0.9,
                'top_p' => 0.9,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'resumePromptBOT',
                'contexto' => 'Você deve resumir o prompt a seguir.',
                'formato_resposta' => 'De acordo com a prompt do usuário, informe um breve resumo, em pt-br.',
                'temperatura' => 0.4,
                'top_p' => 1,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
        ];

        foreach ($bots as $bot) {
            $this->command->info("Processando bot: {$bot['nome']}...");
            Bot::updateOrCreate(['nome' => $bot['nome']], $bot);
        }

        $this->command->info('Todos os bots foram inseridos com sucesso!');

        $this->command->info('Iniciando inserção de perguntas...');

        $perguntas = [
            [
                'pesquisa' => 'smsa',
                'nome' => 'autorizacaoLGPD',
                'mensagem' => "Olá! Eu sou Carlos, quero saber a sua opinião sobre o seu atendimento médico em Araucária hoje.\n"
                    . "Eu sou uma inteligência artificial e suas respostas são totalmente anônimas.\n"
                    . "Usarei essas informações apenas para melhorar os serviços de saúde na cidade.\n"
                    . "Podemos iniciar a pesquisa? Responda \"sim\" ou \"não\"",
            ],
            [
                'pesquisa' => 'smsa',
                'nome' => 'nomeUnidadeSaude',
                'mensagem' => "Que bom que aceitou participar!\n"
                    . "Vou iniciar a primeira pergunta então:\n"
                    . "Qual o nome da unidade de atendimento médico que você esteve hoje?",
            ],
            [
                'pesquisa' => 'smsa',
                'nome' => 'lgpdNegado',
                'mensagem' => "Está sem tempo para responder agora?\n"
                    . "Sem problemas, deixamos para uma próxima oportunidade!\n"
                    . "Agradeço pela atenção!",
            ],
            [
                'pesquisa' => 'smsa',
                'nome' => 'recepcaoUnidade',
                'mensagem' => "Agora, o que você achou da recepção da unidade?\n"
                    . "Você foi bem instruído ao chegar e ao sair do local?",
            ],
            [
                'pesquisa' => 'smsa',
                'nome' => 'limpezaUnidade',
                'mensagem' => "E sobre a limpeza e conservação do local?\n"
                    . "Os banheiros e corredores, estavam em ordem?",
            ],
            [
                'pesquisa' => 'smsa',
                'nome' => 'medicoQualidade',
                'mensagem' => "Quanto ao médico que lhe atendeu?\n"
                    . "Qual o nome dele, você gostou do atendimento, ele foi educado e prestativo?",
            ],
            [
                'pesquisa' => 'smsa',
                'nome' => 'exameQualidade',
                'mensagem' => "Você fez algum tipo de exame?\n"
                    . "O exame foi bem realizado?",
            ],
            [
                'pesquisa' => 'smsa',
                'nome' => 'tempoAtendimento',
                'mensagem' => "Estamos quase acabando, essa é a penúltima pergunta :)\n"
                    . "Caso não seja um atendimento agendado, quanto tempo você aguardou para ser atendido?"
                    . "Se era um atendimento agendado, foi atendido no horário marcado ou atrasou?",
            ],
            [
                'pesquisa' => 'smsa',
                'nome' => 'comentarioLivre',
                'mensagem' => "Última pergunta, na verdade é um espaço aberto, para você escrever qualquer comentário que tenha seobre esse atendimento.\n"
                    . "Fique a vontade.",
            ],
        ];

        foreach ($perguntas as $pergunta) {
            $this->command->info("Processando pergunta: {$pergunta['nome']}...");
            PerguntaPesquisa::updateOrCreate(['nome' => $pergunta['nome']], $pergunta);
        }

        $this->command->info('Todas as perguntas foram inseridas com sucesso!');
    }
}

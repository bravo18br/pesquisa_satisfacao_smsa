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
                'nome' => 'lgpdAutorizacaoBOT', //REVISADO
                'contexto' => '///CONTEXTO:Você está conversando num chat com um usuário. Você tem liberdade para falar sobre qualquer assunto. Você perguntou para o usuário se ele aceita ou não participar de uma pesquisa de satisfação.',
                'formato_resposta' => '///RESPOSTA IDEAL:Responda, apenas sim ou não, minusculo, sem ponto nenhum, se o usuário autorizou a pesquisa de satisfação.',
                'temperatura' => 1,
                'top_p' => 1,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'unidadeAtendimentoBOT', //REVISADO
                'contexto' => '///CONTEXTO:Você pode falar livremente sobre qualquer assunto. Você perguntou para o usuário o nome da unidade de atendimento médico que ele esteve hoje.',
                'formato_resposta' => '///RESPOSTA IDEAL:Responda, apenas o nome da unidade médica. Resposta curta, objetiva.',
                'temperatura' => 1.0,
                'top_p' => 0.5,
                'model' => 'llama3.1',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'recepcaoUnidadeBOT', //REVISADO
                'contexto' => '///CONTEXTO:Você pode falar livremente sobre qualquer assunto. Você perguntou para o usuário o que ele achou da recepção do local, se ele foi bem instruído ao chegar e ao sair da unidade.',
                'formato_resposta' => '///RESPOSTA IDEAL:Usando a mensagem do usuário como parâmetro, informe se ele gostou ou não da recepção da unidade. Resposta curta e objetiva.',
                'temperatura' => 0.0,
                'top_p' => 0.9,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'limpezaConservacaoBOT', //REVISADO
                'contexto' => '///CONTEXTO:Você pode falar livremente sobre qualquer assunto. Você perguntou para o usuário o que ele achou da limpeza e conservação do local, se os banheiros e corredores estavam em ordem.',
                'formato_resposta' => '///RESPOSTA IDEAL:Usando a mensagem do usuário como parâmetro, informe se ele gostou ou não da limpeza e conservação da unidade. Resposta curta e objetiva.',
                'temperatura' => 1.0,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'medicoQualidadeBOT', //REVISADO
                'contexto' => '///CONTEXTO:Você pode falar livremente sobre qualquer assunto. Você perguntou para o usuário o nome do médico e se ele gostou do atendimento médico.',
                'formato_resposta' => '///RESPOSTA IDEAL:Usando a mensagem do usuário como parâmetro, informe se o usuário gostou ou não do médico que lhe atendeu. Resposta curta e objetiva.',
                'temperatura' => 0.8,
                'top_p' => 0.2,
                'model' => 'llama3.1',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'exameQualidadeBOT',
                'contexto' => 'Você pode falar livremente sobre qualquer assunto. Você perguntou para o usuário se ele fez um exame médico hoje, e se ele gostou de como esse exame foi feito.',
                'formato_resposta' => 'De acordo com a prompt do usuário, informe se ele fez um exame hoje, faça um breve resumo sobre a experiência dele, e informe se ele gostou ou não do exame. Resposta curta e objetiva.',
                'temperatura' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'tempoAtendimentoBOT',
                'contexto' => 'Você pode falar livremente sobre qualquer assunto. Você perguntou para o usuário se ele percebeu alguma demora para ser atendido',
                'formato_resposta' => 'De acordo com a prompt do usuário, registre quanto tempo atrasou o atendimento dele. Caso não tenha atrasado, responda "Atendido sem atraso".',
                'temperatura' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'comentarioLivreBOT',
                'contexto' => 'Você pode falar livremente sobre qualquer assunto. Você pediu para o usuário contar, de maneira geral, qual foi a experiência dele no atendimento médico de hoje.',
                'formato_resposta' => 'De acordo com a prompt do usuário, faça um breve resumo sobre a experiência dele. Resposta curta e objetiva.',
                'temperatura' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'encerramentoPesquisaBOT',
                'contexto' => 'Você pode falar livremente sobre qualquer assunto. Você pediu para o usuário contar, de maneira geral, qual foi a experiência dele no atendimento médico de hoje.',
                'formato_resposta' => 'De acordo com a prompt do usuário, avalie o sentimento dele, e envie uma mensagem de encerramento, sendo gentil, informando que a Prefeitura de Araucária agradece a participação dele na pesquisa de satisfação. Resposta curta e objetiva.',
                'temperatura' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 300,
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
                'contexto' => 'Você pode falar livremente sobre qualquer assunto. Você deve classificar o prompt do usuário. As classificações permitidas são: Dúvida, Reclamação, Sugestão, Elogio ou Outro',
                'formato_resposta' => 'De acordo com a prompt do usuário, qual a classificação do prompt, sem ponto, nada mais, em pt-br.',
                'temperatura' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'classificaSentimentoBOT',
                'contexto' => 'Você pode falar livremente sobre qualquer assunto. Você deve analisar o prompt do usuário para identificar o sentimento dele. As opções permitidas são: Satisfeito, Irritado, Triste, Entusiasmado ou Neutro',
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
                'nome' => 'semInteracao',
                'mensagem' => "Não detectei nenhuma resposta já faz algum tempo, estou encerrando a pesquisa.\n"
                    . "Agradeço sua atenção!",
            ],
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
                'mensagem' => "A última pergunta, na verdade é um espaço aberto, para você escrever qualquer comentário que tenha sobre esse atendimento.\n"
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

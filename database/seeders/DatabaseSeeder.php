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
                'nome' => 'contatoInicialBOT', //REVISADO
                'contexto' => '<|start_contexto|>Você é uma IA que executa pesquisa de satisafação sobre a qualidade do atendimento médico para a Secretaria Municipal de Saude da Prefeitura de Araucária.<|end_contexto|>',
                'prompt' => '<|start_prompt|>Envie mensagem inicial para um cidadão, informe que a pesquisa é sigilosa e pergunte se ele autoriza ("sim" ou "não") iniciar a pesquisa, conforme LGPD exige. Mensagem breve e objetiva. Seu único objetivo nessa mensagem é saber se o usuário autoriza a pesquisa.<|end_prompt|>',
                'temperature' => 0,
                'top_p' => 0.2,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 100,
            ],
            [
                'nome' => 'lgpdNegadoBOT', //REVISADO
                'contexto' => '<|start_contexto|>Você está executando uma pesquisa de satisfação para a Prefeitura de Araucaria. Você perguntou para um usuário se ele aceita participar da pesquisa<|end_contexto|>',
                'prompt' => '<|start_prompt|>Responda ao usuário e encerre, resposta breve e objetiva.<|end_prompt|>',
                'temperature' => 0.0,
                'top_p' => 0.0,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 100,
            ],
            [
                'nome' => 'lgpdAutorizacaoBOT', //REVISADO
                'contexto' => '<|start_contexto|>Você é um classificador de mensagens de usuários. Você responde apenas uma palavra "afirmativa" ou "negativa"<|end_contexto|>',
                'prompt' => '<|start_prompt|>Classifique a mensagem do usuario em "afirmativa" ou "negativa"<|end_prompt|>',
                'temperature' => 0.1,
                'top_p' => 0.0,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 10,
            ],
            [
                'nome' => 'unidadeAtendimentoBOT', //REVISADO
                'contexto' => '<|start_contexto|>Você está executando uma pesquisa de satisfação com um usuário e você perguntou se ele aceita participar da pesquisa<|end_contexto|>',
                'prompt' => '<|start_prompt|>Responda a mensagem do usuário de forma breve e objetiva. Continue a pesquisa perguntando qual o nome da unidade médica que ele foi atendido.<|end_prompt|>',
                'temperature' => 0,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 100,
            ],
            [
                'nome' => 'recepcaoUnidadeBOT',
                'contexto' => '///CONTEXTO:Você pode falar livremente sobre qualquer assunto. Você perguntou para o usuário o que ele achou da recepção do local, se ele foi bem instruído ao chegar e ao sair da unidade.',
                'prompt' => '///RESPOSTA IDEAL:Usando a mensagem do usuário como parâmetro, informe se ele gostou ou não da recepção da unidade. Resposta curta e objetiva.',
                'temperature' => 0.0,
                'top_p' => 0.9,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'limpezaConservacaoBOT',
                'contexto' => '///CONTEXTO:Você pode falar livremente sobre qualquer assunto. Você perguntou para o usuário o que ele achou da limpeza e conservação do local, se os banheiros e corredores estavam em ordem.',
                'prompt' => '///RESPOSTA IDEAL:Usando a mensagem do usuário como parâmetro, informe se ele gostou ou não da limpeza e conservação da unidade. Resposta curta e objetiva.',
                'temperature' => 1.0,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'medicoQualidadeBOT',
                'contexto' => '///CONTEXTO:Você pode falar livremente sobre qualquer assunto. Você perguntou para o usuário o nome do médico e se ele gostou do atendimento médico.',
                'prompt' => '///RESPOSTA IDEAL:Usando a mensagem do usuário como parâmetro, informe se o usuário gostou ou não do médico que lhe atendeu. Resposta curta e objetiva.',
                'temperature' => 0.8,
                'top_p' => 0.2,
                'model' => 'llama3.1',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'exameQualidadeBOT',
                'contexto' => 'Você pode falar livremente sobre qualquer assunto. Você perguntou para o usuário se ele fez um exame médico hoje, e se ele gostou de como esse exame foi feito.',
                'prompt' => 'De acordo com a prompt do usuário, informe se ele fez um exame hoje, faça um breve resumo sobre a experiência dele, e informe se ele gostou ou não do exame. Resposta curta e objetiva.',
                'temperature' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'tempoAtendimentoBOT',
                'contexto' => 'Você pode falar livremente sobre qualquer assunto. Você perguntou para o usuário se ele percebeu alguma demora para ser atendido',
                'prompt' => 'De acordo com a prompt do usuário, registre quanto tempo atrasou o atendimento dele. Caso não tenha atrasado, responda "Atendido sem atraso".',
                'temperature' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'comentarioLivreBOT',
                'contexto' => 'Você pode falar livremente sobre qualquer assunto. Você pediu para o usuário contar, de maneira geral, qual foi a experiência dele no atendimento médico de hoje.',
                'prompt' => 'De acordo com a prompt do usuário, faça um breve resumo sobre a experiência dele. Resposta curta e objetiva.',
                'temperature' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'encerramentoPesquisaBOT', //REVISADO
                'contexto' => '<|start_contexto|>Você está fazendo uma pesquisa de satisfação e você pediu para o usuário contar, de maneira geral, qual foi a experiência dele no atendimento médico de hoje.<|end_contexto|>',
                'prompt' => '<|start_prompt|>Responda a mensagem do usuario, encerrando a pesquisa de satisfação, sendo gentil, informando que a Prefeitura de Araucária agradece a participação dele na pesquisa de satisfação. Resposta curta e objetiva.<|end_prompt|>',
                'temperature' => 0.3,
                'top_p' => 0.0,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 100,
            ],
            [
                'nome' => 'detectaEnderecoBOT',
                'contexto' => 'Você perguntou o endereço do usuário.',
                'prompt' => 'De acordo com a prompt do usuário, qual o endereço informado? Caso não encontre, responda "Não encontrado", sem ponto, nada mais, em pt-br.',
                'temperature' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'classificaTipoMensagemBOT',
                'contexto' => 'Você pode falar livremente sobre qualquer assunto. Você deve classificar o prompt do usuário. As classificações permitidas são: Dúvida, Reclamação, Sugestão, Elogio ou Outro',
                'prompt' => 'De acordo com a prompt do usuário, qual a classificação do prompt, sem ponto, nada mais, em pt-br.',
                'temperature' => 0.5,
                'top_p' => 0.5,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'classificaSentimentoBOT',
                'contexto' => 'Você pode falar livremente sobre qualquer assunto. Você deve analisar o prompt do usuário para identificar o sentimento dele. As opções permitidas são: Satisfeito, Irritado, Triste, Entusiasmado ou Neutro',
                'prompt' => 'De acordo com a prompt do usuário, qual o sentimento do usuário, sem ponto, nada mais, em pt-br.',
                'temperature' => 0.5,
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
                'prompt' => 'De acordo com a prompt do usuário, indique o nome da secretaria responsável, sem ponto, nada mais, em pt-br.',
                'temperature' => 0.9,
                'top_p' => 0.9,
                'model' => 'llama3.2',
                'stream' => false,
                'max_length' => 200,
            ],
            [
                'nome' => 'resumePromptBOT',
                'contexto' => 'Você deve resumir o prompt a seguir.',
                'prompt' => 'De acordo com a prompt do usuário, informe um breve resumo, em pt-br.',
                'temperature' => 0.4,
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

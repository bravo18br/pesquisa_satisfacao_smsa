# Pesquisa de Satisfação Automatizada via WhatsApp

## Visão Geral
Este projeto tem como objetivo a implementação de uma **pesquisa de satisfação automatizada** para todos os pacientes da **Rede de Atenção à Saúde de Araucária** e serviços credenciados (hospitais, clínicas, laboratórios). O objetivo é avaliar a qualidade dos serviços prestados através de um sistema integrado com o **WhatsApp**, permitindo uma comunicação rápida e eficiente com os pacientes. No futuro, a pesquisa pode ser ampliada para TOTENS nas próprias unidades.

## GLPI Associado
Esse projeto está em desenvolvimento por políticas da atual gestão que foca no uso de IA para melhorar a vida dos cidadãos. Também é uma solicitação da SMSA, registrada no GLPI 78039

## LGPD
A primeira pergunta da pesquisa de satisfação é sobre o concentimento no fornecimento dos dados. Contudo, entendo que isso pode ser dispensado, pois o artigo 23 da LGPD (Lei 13.709/2018) reforça que órgãos públicos podem tratar dados para a execução de políticas públicas e que esses dados podem ser compartilhados entre órgãos, desde que garantam segurança e sigilo e preferencialmente anonimizados. Uma mensagem inicial clara para o usuário, é recomendada. EX: "Esta pesquisa faz parte de uma iniciativa da Prefeitura de Araucária para avaliar e aprimorar os serviços de saúde pública, conforme a Lei Geral de Proteção de Dados (Lei 13.709/2018, art. 7º, III e VIII)."

## Requisitos
Precisamos de um trigger acionado pelo IPM para iniciar a pesquisa de satisfação. Em contato com a IPM, já existe uma pesquisa de satisfação ativada por email e uma por SMS. Em conversa informal, não existe possibilidade de acionar um trigger com o telefone de contato e nome da unidade. Vai ter que ser negociado esse trigger. Pode ser um webhook, API, um email para um destinatário específico, tanto faz, desde que a gente 

## Tecnologias Utilizadas
- **Evolution API**: Para o envio e recebimento de mensagens via WhatsApp.
- **Ollama (LLaMA 3.2)**: Para o tratamento e processamento das respostas dos pacientes.
- **PostgreSQL**: Para armazenamento das respostas e análise posterior via BI (Business Intelligence).

## Fluxo de Funcionamento
1. O paciente recebe uma mensagem no **WhatsApp** logo após o atendimento, contendo a pesquisa de satisfação.
2. O paciente responde às perguntas conforme a escala definida.
3. As respostas são processadas pelo **Ollama (LLaMA 3.2)** para padronização e classificação dos dados.
4. Os resultados são armazenados no **PostgreSQL** para geração de relatórios e BI.

## Perguntas da Pesquisa
1. **LGPD - Lei Geral de Proteção de Dados**
   - Regulamenta sobre o acesso e o tratamento de dados do usuário - necessário para verificação do aceite.
   - **Considere a Lei 13709/2018 - Lei Geral de Proteção de Dados e declare o aceite em participar da pesquisa:**
     1 - Sim
     2 - Não

2. **Avaliação do Atendimento da Equipe**
   - Em uma escala de 1 a 5, como você avalia a sua experiência do atendimento da equipe que o recepcionou em sua chegada e o orientou na saída?
     - 5 - Extremamente satisfeito
     - 4 - Satisfeito
     - 3 - Neutro
     - 2 - Insatisfeito
     - 1 - Extremamente insatisfeito

3. **Avaliação da Realização do Exame**
   - Em uma escala de 1 a 5, como você avalia a sua experiência durante a realização do exame?
     - 5 - Extremamente satisfeito
     - 4 - Satisfeito
     - 3 - Neutro
     - 2 - Insatisfeito
     - 1 - Extremamente insatisfeito

4. **Avaliação do Ambiente da Clínica**
   - Em uma escala de 1 a 5, como você avalia o ambiente e o estado de conservação e limpeza da clínica?
     - 5 - Extremamente satisfeito
     - 4 - Satisfeito
     - 3 - Neutro
     - 2 - Insatisfeito
     - 1 - Extremamente insatisfeito

5. **Pontualidade no Atendimento**
   - Considerando a seguinte escala, você considera que o exame foi realizado no horário agendado?
     - 5 - Sim
     - 4 - Não, até 15 minutos de atraso
     - 3 - Não, até 30 minutos de atraso
     - 2 - Não, até 45 minutos de atraso
     - 1 - Não, mais de 1 hora de atraso

6. **Avaliação Geral do Atendimento**
   - Em uma escala de 1 a 5, como você avalia o seu atendimento de forma geral?
     - 5 - Extremamente satisfeito
     - 4 - Satisfeito
     - 3 - Neutro
     - 2 - Insatisfeito
     - 1 - Extremamente insatisfeito

7. **Observação Livre**     
   - Agora, escrevendo com as suas palavras, quer fazer algum elogio, sugestão ou reclamação? Sinta-se a vontade, pode escrever!

## Instalação e Configuração
1. Clone este repositório:
   ```sh
   git clone https://github.com/seu-repositorio.git
   cd nome-do-projeto
   ```
2. Instale as dependências:
   ```sh
   npm install
   ```
3. Configure as variáveis de ambiente (`.env`):
   ```env
   EVOLUTION_API_KEY=your_api_key
   POSTGRES_HOST=localhost
   POSTGRES_DB=paciente_satisfacao
   POSTGRES_USER=usuario
   POSTGRES_PASSWORD=senha
   ```
4. Execute a aplicação:
   ```sh
   npm run dev
   ```

## Futuras Melhorias
- Dashboard para BI com visualização interativa das respostas.
- Integração com outros sistemas da Saúde.
- Geração de relatórios automáticos para gestão.

## Licença
Este projeto segue a Licença MIT.

---

Qualquer dúvida, fique à vontade para entrar em contato! 🚀


# Pesquisa de Satisfação Automatizada via WhatsApp

## GLPI Associado
Esse projeto está em desenvolvimento por políticas da atual gestão que foca no uso de IA para melhorar a vida dos cidadãos. Também é uma solicitação da SMSA, registrada no GLPI 78039

## Visão Geral
Este projeto tem como objetivo a implementação de uma **pesquisa de satisfação automatizada** para todos os pacientes da **Rede de Atenção à Saúde de Araucária** e serviços credenciados (hospitais, clínicas, laboratórios). O objetivo é avaliar a qualidade dos serviços prestados através de um sistema integrado com o **WhatsApp**, permitindo uma comunicação rápida e eficiente com os pacientes.

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


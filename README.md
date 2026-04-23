# Entregas do Teste Técnico: Gestão Veterinária

Este documento detalha o histórico de desenvolvimento e as implementações realizadas para cumprir os requisitos do teste divididas por blocos de commits.

## Histórico de Commits

### Commit 1: Correções de Segurança e Acesso
**Mensagem:** `fix: corrige vulnerabilidade na URL definitiva, criando uma policy e aplicando middleware nas rotas`

* **O que foi feito:**
  * Correção da vulnerabilidade que permitia que usuários não logados (visitantes) acessassem as telas do sistema. Agora, todas as rotas sensíveis exigem autenticação.
  * Correção da vulnerabilidade IDOR (Insecure Direct Object Reference) no cadastro de pacientes.
  * Implementação de uma `PatientPolicy` garantindo que:
    * Um Cliente só pode visualizar, editar ou remover o seu próprio cachorro.
    * O sistema barra automaticamente tentativas de alterar o ID na URL para acessar dados de terceiros.
    * O Veterinário possui permissão global para acessar e editar informações de qualquer paciente para fins médicos.

---

### Commit 2: Melhorias no Cadastro e Upload de Foto
**Mensagem:** `feat: adiciona funcionalidade de upload de foto para pacientes e validação no formulário`

* **O que foi feito:**
  * **Nova Funcionalidade:** Implementação do campo de upload de imagem na tela de edição do paciente, aceitando apenas arquivos de imagem.
  * Armazenamento das fotos configurado utilizando a estrutura nativa do Laravel (pasta `storage/public`, para isso rodar `php artisan storage:link`).
  * Adição de validação estrita no Controller para garantir que nenhum paciente seja salvo com campos obrigatórios em branco, cumprindo o requisito de que "todos os campos são obrigatórios".

---

### Commit 3: Módulo Completo de Agendamentos (Cliente e Veterinário) 
**Mensagem:** `feat: implementa módulo completo de agendamentos (fluxo do cliente com AJAX e painel de atendimento do veterinário)`

* **O que foi feito:**
  * **Estrutura:** Criação da tabela `appointments` no banco de dados para vincular Pacientes (Cachorros) a Veterinários através de data, hora e observações.
  * **Área do Cliente (AJAX):**
    * Conserto do botão "Agendar".
    * Criação de uma API interna para consultar horários livres. Ao selecionar a data no formulário, o sistema via AJAX lista dinamicamente apenas os horários comerciais disponíveis.
    * Adição de validação no back-end para prevenir colisões (caso dois clientes cliquem em "Agendar" no mesmo segundo para o mesmo horário).
    * Listagem visual de todas as consultas futuras do cliente na sua tela inicial.
  * **Área do Veterinário:**
    * Construção do painel que lista, de forma cronológica, todas as consultas ativas no sistema.
    * Implementação da tela de atendimento, onde o veterinário insere suas "Observações".

---

### Commit 4: Refinamento de Regras de Negócio e Validações Temporais
**Mensagem:** `feat: adiciona validações para data de nascimento no futuro e agendamentos no passado`

* **O que foi feito:**
    * **Consistência no Cadastro de Pacientes:** Implementação de trava de segurança para impedir o registro de datas de nascimento no futuro, garantindo a coerência dos dados.
    * **Prevenção de Agendamentos Retrógrados:** Atualização da lógica de agendamento para rejeitar tentativas de marcação de consultas em datas ou horários que já passaram.

---

### Commit 5: Gestão Administrativa para Veterinários
**Mensagem:** `feat: adiciona funcionalidades para veterinários gerenciarem pacientes e agendamentos`

* **O que foi feito:**
    * **Gestão de Fluxo:** O Veterinário agora pode realizar agendamentos em nome de qualquer cliente e cadastrar novos pacientes vinculando-os a proprietários específicos.
    * **Painel de Controle:** Refatoração da interface do veterinário para atuar como um centro de comando administrativo, centralizando ações de cadastro e marcação de consultas.

---

### Commit 6: Auditoria e Responsabilidade Técnica
**Mensagem:** `feat: adiciona exibição do veterinário responsável nas consultas dos pacientes e no painel do veterinário`

* **O que foi feito:**
    * **Rastreabilidade:** Implementação de registro de auditoria para identificar exatamente qual profissional finalizou cada atendimento médico.
    * **Transparência na Interface:**
        * **Visão do Cliente:** As consultas finalizadas agora exibem o nome do veterinário responsável.
        * **Visão do Veterinário:** O painel de histórico passou a listar o médico responsável por cada procedimento realizado na clínica.

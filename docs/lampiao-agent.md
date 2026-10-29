# Documentação do Agente Lampião (Assistente Bíblico)

## 1. Visão Geral

O **Lampião** é o assistente virtual de inteligência artificial do **Projeto Lâmpada**. Ele atua como um tutor bíblico empático, sábio e acolhedor, projetado para auxiliar os usuários na compreensão e aprofundamento das leituras devocionais diárias.

A implementação é baseada no pacote oficial **Laravel AI SDK** (`laravel/ai`), integrado prioritariamente com os modelos de linguagem do **Google Gemini**.

---

## 2. Arquitetura da Solução

O fluxo de comunicação da IA envolve três camadas principais:

```
┌─────────────────────────────────┐
│     Frontend (Alpine.js)        │
│  resources/js/modules/ai-chat.js│
└────────────────┬────────────────┘
                 │ POST /api/ai/chat (payload com mensagem + contexto)
                 ▼
┌─────────────────────────────────┐
│       AiChatController          │  ◄── Cache (1 hora via Redis/DB)
│ app/Http/Controllers/           │
└────────────────┬────────────────┘
                 │ Executa prompt()
                 ▼
┌─────────────────────────────────┐
│         LampiaoAgent            │
│       app/Ai/LampiaoAgent.php   │
└────────────────┬────────────────┘
                 │ Chamada API
                 ▼
┌─────────────────────────────────┐
│    Provedor LLM (Google Gemini) │
└─────────────────────────────────┘
```

---

## 3. Componentes do Sistema

### 3.1. Agente (`App\Ai\LampiaoAgent`)
- **Arquivo**: [`app/Ai/LampiaoAgent.php`](../app/Ai/LampiaoAgent.php)
- **Interfaces**:
  - `Laravel\Ai\Contracts\Agent`: Contrato central para definição do agente.
  - `Laravel\Ai\Contracts\Conversational`: Habilita suporte a histórico e continuidade de sessões de diálogo.
- **Traits**:
  - `Laravel\Ai\Promptable`: Permite invocar `$agent->prompt(...)`.
  - `Laravel\Ai\Concerns\RemembersConversations`: Gerencia o estado e persistência das mensagens por `conversation_id` ou associadas ao usuário (`forUser`).

#### Persona e Instruções de Sistema (*System Prompt*)
O método `instructions()` define as diretrizes de comportamento do Lampião:
- **Papel**: Tutor bíblico fundamentado em princípios cristãos evangélicos.
- **Tom de Voz**: Empático, acolhedor e encorajador.
- **Objetividade**: Respostas curtas e diretas, sem fazer perguntas de volta ao leitor.
- **Formatação Mobile**:
  - Quebras de linha frequentes (evita blocos densos de texto).
  - Listas/marcadores (*bullet points*) para tópicos explicativos.
  - Ênfase com negrito Markdown (`**termo**`).
  - Uso moderado de emojis ilustrativos.

---

### 3.2. Controller (`App\Http\Controllers\AiChatController`)
- **Arquivo**: [`app/Http/Controllers/AiChatController.php`](../app/Http/Controllers/AiChatController.php)
- **Rota**: `POST /api/ai/chat` (nomeada como `api.ai.chat`)
- **Responsabilidades**:
  1. **Validação**:
     - `message`: Obrigatório, string, máximo de 1000 caracteres.
     - `devotional_context`: Opcional, string com o conteúdo bíblico em foco.
     - `conversation_id`: Opcional, string para prosseguir uma conversa existente.
  2. **Injeção de Contexto (*In-Context Prompting*)**:
     Quando um `devotional_context` é fornecido, ele é formatado antes da pergunta do usuário:
     ```text
     CONTEXTO DO DEVOCIONAL:
     {devotional_context}

     PERGUNTA: {message}
     ```
  3. **Continuidade da Conversa**:
     - Se `conversation_id` for informado: `$agent->continue($conversationId, $user)`.
     - Caso contrário, se o usuário estiver autenticado: `$agent->forUser($user)`.
  4. **Camada de Cache**:
     - Implementa `Cache::remember` por 1 hora gerando uma hash baseada em `md5($message . $context . $conversationId)`.
     - Evita custos de API e requisições repetidas idênticas.

---

### 3.3. Interface e Frontend (`resources/js/modules/ai-chat.js`)
- **Arquivo**: [`resources/js/modules/ai-chat.js`](../resources/js/modules/ai-chat.js)
- **Módulo**: Alpine.js (`aiChat`)
- **Comportamento**:
  - Captura os textos do Antigo e Novo Testamento do devocional atual (`content_old_testament` e `content_new_testament`), remove as tags HTML e envia como `devotional_context`.
  - Envia a requisição via `fetch` assíncrono para `/api/ai/chat`.
  - Exibe estados de carregamento (`isAiLoading`) e rolagem automática ao receber a resposta.

---

## 4. Configuração e Variáveis de Ambiente

O agente utiliza a configuração centralizada em [`config/ai.php`](../config/ai.php).

### Variáveis no `.env`:
```env
# Provedor padrão (Gemini)
AI_DEFAULT_PROVIDER=gemini

# Chave de API do Google Gemini
GEMINI_API_KEY=sua-chave-api-aqui

# Opcional: Provedores alternativos suportados
OPENAI_API_KEY=
ANTHROPIC_API_KEY=
DEEPSEEK_API_KEY=
GROQ_API_KEY=
```

---

## 5. Especificação da API

### Endpoint: `POST /api/ai/chat`

#### Headers
```http
Content-Type: application/json
X-CSRF-TOKEN: {token}
```

#### Corpo da Requisição (*Payload*)
```json
{
  "message": "Qual é a lição prática desse texto para o meu dia a dia?",
  "devotional_context": "Salmos 23: O Senhor é o meu pastor; nada me faltará...",
  "conversation_id": "opcional-uuid-da-conversa"
}
```

#### Resposta de Sucesso (`200 OK`)
```json
{
  "response": "A principal lição é o descanso na provisão e no cuidado de Deus:\n\n* **Confiança diária**: Saber que Ele guia seus passos.\n* **Paz interior**: Mesmo diante de adversidades, você não está só. 🙏",
  "conversation_id": "019183ab-..."
}
```

#### Resposta de Erro (`500 Internal Server Error`)
```json
{
  "error": "Ocorreu um erro ao processar sua pergunta.",
  "details": "Mensagem detalhada do erro (em ambiente de debug)"
}
```

---

## 6. Padrão Atual vs. RAG (Retrieval-Augmented Generation)

> [!NOTE]
> A implementação atual utiliza **Injeção Direta de Contexto** (*Prompt Stuffing* pontual), e não **RAG**.

- **Como funciona hoje**: O frontend extrai o texto do devocional que está aberto na tela e anexa-o estaticamente no prompt.
- **O que seria necessário para evoluir para RAG**:
  1. Gerar **embeddings** textuais (vetorização) de toda a Bíblia e dos devocionais históricos.
  2. Armazenar os vetores em banco de dados vetorial ou extensão (ex: PostgreSQL com `pgvector`, Qdrant, Pinecone).
  3. Fazer busca semântica dinâmica conforme a pergunta do usuário para recuperar os trechos mais pertinentes antes de alimentar o modelo.

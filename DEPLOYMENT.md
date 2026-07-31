# 🚀 Guia de Deploy - Projeto Lâmpada (GCP Cloud Run & Cloud SQL)

Este documento fornece as instruções para realizar o deploy em produção do **Projeto Lâmpada** no **Google Cloud Platform (GCP)** utilizando **Cloud Run**, **Cloud SQL (MySQL 8.4)** e **GitHub Actions (CI/CD)**.

---

## 🏗️ Arquitetura de Implantação

```
[ Usuários Web ]
       │
       ▼
[ Google Cloud Run ] ──(Unix Socket)──► [ Google Cloud SQL (MySQL 8.4) ]
       │
       ├──────────────────────────────► [ Google Cloud Storage (Assets/Áudios) ]
       │
       └──────────────────────────────► [ Google Gemini / TTS APIs ]
```

---

## 🛠️ Pré-requisitos no GCP

Antes de rodar o pipeline de deploy, certifique-se de configurar os seguintes recursos no GCP Console ou via `gcloud CLI`:

1. **Projeto GCP Ativo**: Ter um ID de projeto configurado (ex: `projeto-lampada-001`).
2. **APIs Habilitadas**:
   - Cloud Run API (`run.googleapis.com`)
   - Artifact Registry API (`artifactregistry.googleapis.com`)
   - Cloud SQL Admin API (`sqladmin.googleapis.com`)
   - Secret Manager API (`secretmanager.googleapis.com`)
   - Text-to-Speech API (`texttospeech.googleapis.com`)
3. **Instância no Cloud SQL**:
   - Banco de Dados MySQL 8.4 criado.
   - Banco e usuário/senha configurados.
4. **Repositório no Artifact Registry**:
   - Criado na região desejada (ex: `southamerica-east1` ou `us-central1`).
5. **Service Account de CI/CD**:
   - Conta de serviço com as permissões: `Cloud Run Developer`, `Artifact Registry Writer`, `Service Account User`, `Cloud SQL Client`.
   - Chave de conta de serviço gerada em formato JSON (`GCP_SA_KEY`).

---

## 🔐 Configuração dos Secrets e Variáveis no GitHub

No seu repositório do GitHub em **Settings > Secrets and variables > Actions**, configure os seguintes itens:

### Repository Secrets (Chaves Sensíveis):
- `GCP_SA_KEY`: Conteúdo completo do JSON da Service Account de deploy do GCP.
- `DB_PASSWORD`: Senha do usuário do banco de dados MySQL no Cloud SQL.
- `GEMINI_API_KEY`: Chave da API do Google Gemini para o assistente de IA.
- `GOOGLE_CLIENT_ID`: ID do cliente Google OAuth (Laravel Socialite).
- `GOOGLE_CLIENT_SECRET`: Segredo do cliente Google OAuth.
- `GOOGLE_TTS_API_KEY`: API Key para a sintetização de voz (Text-to-Speech).
- `GOOGLE_CLOUD_KEY_JSON`: JSON de credenciais da Service Account do Google Cloud.

### Repository Variables (Configurações do Ambiente):
- `PROJECT_ID`: ID do projeto no GCP.
- `REGION`: Região do GCP (ex: `us-central1` ou `southamerica-east1`).
- `AR_REPO_NAME`: Nome do repositório no Artifact Registry.
- `SERVICE_NAME`: Nome do serviço no Cloud Run (ex: `lampada-app`).
- `CLOUD_SQL_INSTANCE`: Nome da conexão da instância Cloud SQL (`PROJECT_ID:REGION:INSTANCE_NAME`).
- `APP_ENV`: `production`
- `APP_URL`: URL oficial da aplicação (ex: `https://projetolampada.com.br`).
- `DB_DATABASE`: Nome do banco de dados (ex: `lampada`).
- `DB_USERNAME`: Usuário do banco de dados.
- `GOOGLE_REDIRECT_URL`: `https://projetolampada.com.br/auth/google/callback`
- `GOOGLE_CLOUD_STORAGE_BUCKET`: Nome do bucket no GCS.

---

## 🔄 Deploy Automatizado via GitHub Actions (CI/CD)

O deploy é acionado automaticamente em cada push para a branch `main` através da workflow definida em `.github/workflows/deploy.yml`.

### Etapas executadas no Workflow:
1. Autenticação no GCP com `GCP_SA_KEY`.
2. Build da imagem Docker contendo Nginx + PHP-FPM 8.4 + Supervisor.
3. Envios (*Push*) da imagem para o Google Artifact Registry.
4. Deploy no Google Cloud Run conectando a instância ao Cloud SQL.
5. Execução automática de migrações e otimização de caches via script de inicialização (`run.sh`).

---

## 🐳 Build e Teste Local da Imagem Docker

Para testar a imagem de produção localmente utilizando o Docker:

```bash
# 1. Build da imagem Docker
docker build -t lampada-app:latest .

# 2. Execução da imagem conectada a um banco local
docker run -p 8080:8080 --rm \
  --env APP_ENV=local \
  --env APP_KEY="base64:..." \
  --env DB_CONNECTION=mysql \
  --env DB_HOST=host.docker.internal \
  --env DB_DATABASE=lampada \
  --env DB_USERNAME=sail \
  --env DB_PASSWORD=password \
  lampada-app:latest
```

---

## 🛠️ Resolução de Problemas Comuns em Deploy

- **Erro de Conexão com o Banco de Dados no Cloud Run**:
  Verifique se a flag `--add-cloudsql-instances` no workflow contém exatamente a string de conexão da instância (`PROJETO:REGIAO:INSTANCIA`) e se a `DB_SOCKET` está apontando para `/cloudsql/PROJETO:REGIAO:INSTANCIA`.
- **Erro 500 no Primeiro Acesso**:
  Certifique-se de que a `APP_KEY` de produção esteja definida e que as migrações tenham sido executadas pelo container (`php artisan migrate --force`).

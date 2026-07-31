# 🤝 Guia de Contribuição - Projeto Lâmpada

Ficamos muito felizes pelo seu interesse em contribuir com o **Projeto Lâmpada**! Este documento orienta o processo de desenvolvimento e os padrões que utilizamos para manter o código limpo, seguro e sustentável.

---

## 📋 Sumário
1. [Código de Conduta](#-código-de-conduta)
2. [Fluxo de Trabalho Git](#-fluxo-de-trabalho-git)
   - [Nomenclatura de Branches](#21-nomenclatura-de-branches)
   - [Padrão de Commits (Conventional Commits)](#22-padrão-de-commits-conventional-commits)
   - [Submissão de Pull Requests](#23-submissão-de-pull-requests)
3. [Padrões de Código e Ferramentas](#-padrões-de-código-e-ferramentas)
   - [Laravel Pint (Formatação)](#31-laravel-pint-formatação)
   - [Pest PHP (Testes Automatizados)](#32-pest-php-testes-automatizados)
4. [Como Relatar Bugs e Sugerir Funcionalidades](#-como-relatar-bugs-e-sugerir-funcionalidades)

---

## 📜 Código de Conduta

Ao participar deste projeto, você concorda em seguir nosso [Código de Conduta](CODE_OF_CONDUCT.md). Mantemos um ambiente acolhedor, respeitoso e inclusivo para todos.

---

## 🔀 Fluxo de Trabalho Git

### 2.1. Nomenclatura de Branches
Sempre crie uma nova branch a partir da `main` para trabalhar em alterações. Utilize os seguintes prefixos:

- `feature/` -> Novas funcionalidades ou melhorias (ex: `feature/audio-player-custom`)
- `bugfix/` -> Correções de bugs (ex: `bugfix/login-google-callback`)
- `hotfix/` -> Correções críticas diretas para produção (ex: `hotfix/tts-api-error`)
- `chore/` -> Manutenção, atualização de dependências e refatorações (ex: `chore/deps-laravel-13`)
- `docs/` -> Alterações exclusivas na documentação (ex: `docs/deployment-guide`)

---

### 2.2. Padrão de Commits (Conventional Commits)

Todos os commits devem seguir a especificação de **Conventional Commits** e ser escritos em **Português (Brasil)**.

#### Formato:
`<tipo>(<escopo>): <descrição em imperativo e minúsculas>`

#### Tipos permitidos:
- **feat**: Nova funcionalidade.
- **fix**: Correção de bug.
- **docs**: Alteração em documentação.
- **style**: Ajustes de formatação, espaços ou ponto e vírgula (sem impacto no código).
- **refactor**: Refatoração de código que não altera funcionalidade nem corrige bug.
- **perf**: Melhoria de desempenho.
- **test**: Adição ou correção de testes automatizados.
- **build**: Alterações no sistema de build ou dependências externas.
- **ci**: Alterações nos arquivos de configuração de CI/CD (ex: GitHub Actions).
- **chore**: Outras tarefas de manutenção que não alteram código-fonte ou testes.

#### Regras de Formatação:
1. **Modo Imperativo**: Use verbo no imperativo e presente (ex: `add`, `fix`, `altera`, `corrige`).
2. **Minúsculas**: A descrição deve começar em minúscula e não ter ponto final.
3. **Escopo**: Especifique o componente afetado entre parênteses (ex: `auth`, `bible`, `tts`, `ai`, `ui`).
4. **Tamanho**: Máximo de 72 caracteres no título do commit.

#### Exemplos de Commits Válidos:
- `feat(ai): adiciona suporte a streaming no chat bíblico`
- `fix(tts): corrige codificação de caracteres em textos com acento`
- `refactor(bible): otimiza consulta eloquente de devocionais`
- `chore(deps): atualiza pacote filament para versao 5.1`
- `docs(readme): adiciona instrucoes de execucao com sail`

---

### 2.3. Submissão de Pull Requests

1. **Branches Protegidas**: Commits diretos na branch `main` não são permitidos. Todas as alterações devem entrar via **Pull Request (PR)**.
2. **Foco e Tamanho**: Mantenha os PRs pequenos, focados e objetivos.
3. **Descrição Clara**:
   - Inclua um resumo em tópicos do que foi alterado.
   - Detalhe como a alteração foi testada/validada.
4. **Verificação de CI**: Certifique-se de que todos os testes e linters estejam passando antes de solicitar a revisão.

---

## 🛠️ Padrões de Código e Ferramentas

### 3.1. Laravel Pint (Formatação)

Antes de abrir um PR ou realizar o commit, formate todo o código PHP modificado com o **Laravel Pint**:

```bash
./vendor/bin/sail bin pint --dirty
```

Para verificar se o código atende ao padrão sem aplicar alterações:

```bash
./vendor/bin/sail bin pint --test
```

---

### 3.2. Pest PHP (Testes Automatizados)

Garantimos a estabilidade da aplicação através de testes automatizados com o **Pest PHP**.

Para executar toda a suíte de testes:

```bash
./vendor/bin/sail artisan test --compact
```

Para executar um teste específico:

```bash
./vendor/bin/sail artisan test --compact --filter=BibleServiceTest
```

> ⚠️ **Importante:** Nenhum Pull Request será aprovado se houver testes falhando.

---

## 🐛 Como Relatar Bugs e Sugerir Funcionalidades

- **Encontrou um bug?** Abra uma *Issue* no GitHub detalhando o comportamento esperado, o comportamento atual, passos para reproduzir e logs de erro.
- **Quer sugerir uma funcionalidade?** Abra uma *Issue* de sugestão com a tag `enhancement` descrevendo o caso de uso e os benefícios para a comunidade.

Agradecemos a sua contribuição para tornar o **Projeto Lâmpada** cada vez melhor! 💡

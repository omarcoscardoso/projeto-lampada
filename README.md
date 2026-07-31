<p align="center">
  <img src="https://storage.googleapis.com/iprviamao-com-br/lampada/logo_lampada_app.webp" alt="Logo Projeto Lâmpada" width="280">
</p>
<p align="center">
  <i>"Lâmpada para os meus pés é a tua palavra e luz para o meu caminho." — Salmos 119:105</i>
</p>

<p align="center">
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13"></a>
  <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4"></a>
  <a href="https://livewire.laravel.com"><img src="https://img.shields.io/badge/Livewire-4.x-4E56A6?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire 4"></a>
  <a href="https://filamentphp.com"><img src="https://img.shields.io/badge/Filament-5.x-FDAE4B?style=for-the-badge&logo=laravel&logoColor=black" alt="Filament 5"></a>
  <a href="https://tailwindcss.com"><img src="https://img.shields.io/badge/Tailwind_CSS-4.x-38BDF8?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge" alt="Licença MIT"></a>
</p>

---

## 🌟 Sobre o Projeto Lâmpada

O **Projeto Lâmpada** (`projetolampada.com.br`) é uma plataforma web open-source desenvolvida para conectar fé, estudo bíblico e tecnologia moderna. O objetivo é fornecer uma experiência interativa e enriquecedora para reflexão diária, estudo da Bíblia e suporte teológico impulsionado por Inteligência Artificial.

Construído sobre o ecossistema **Laravel**, a plataforma oferece uma interface reativa, ágil e elegante para estudo individual ou comunitário.

---

## ✨ Principais Funcionalidades

- 📖 **Leitura Bíblica Integrada**: Leitura dinâmica de livros, capítulos e versículos bíblicos.
- 💡 **Devocionais Diários**: Reflexões e leituras organizadas por data.
- 🤖 **Assistente Bíblico com Inteligência Artificial**: Chat integrado com a API do **Google Gemini** para auxílio no entendimento de passagens, contexto histórico e estudos temáticos.
- 🔊 **Narração em Áudio (TTS)**: Recursos de Text-to-Speech via **Google Cloud Speech/TTS** para ouvir devocionais e capítulos.
- 🔑 **Autenticação Simplificada**: Integração com **Google OAuth 2.0** via Laravel Socialite.
- 📊 **Painel Administrativo Elegante**: Gerenciamento completo de conteúdos, devocionais e métricas via **Filament 5**.
- 🚀 **Pronto para Cloud & Containers**: Arquitetura em Docker otimizada para deploy contínuo no **Google Cloud Run** e **Cloud SQL**.

---

## 🛠️ Stack Tecnológico

- **Backend Framework**: [Laravel 13](https://laravel.com) (PHP 8.4)
- **Painel Administrativo**: [Filament 5](https://filamentphp.com)
- **Frontend & Reatividade**: [Livewire 4](https://livewire.laravel.com), [Alpine.js](https://alpinejs.dev)
- **Estilização**: [Tailwind CSS 4](https://tailwindcss.com)
- **Inteligência Artificial**: [Laravel AI SDK](https://github.com/laravel/ai) com integração Google Gemini
- **Banco de Dados**: MySQL 8.0 (Google Cloud SQL em Produção)
- **Armazenamento**: Google Cloud Storage
- **Deploy & Infraestrutura**: Docker, Google Cloud Run, GitHub Actions (CI/CD)

---

## 🚀 Como Executar Localmente

### Pré-requisitos

- Docker Desktop / Docker Engine instalado
- Git

### Passo a Passo

1. **Clonar o Repositório**:
   ```bash
   git clone https://github.com/omarcoscardoso/projeto-lampada.git
   cd projeto-lampada
   ```

2. **Configurar as Variáveis de Ambiente**:
   ```bash
   cp .env.example .env
   ```

3. **Subir os Containers com Laravel Sail**:
   ```bash
   ./vendor/bin/sail up -d
   ```

4. **Instalar Dependências Composer e NPM**:
   ```bash
   ./vendor/bin/sail composer install
   ./vendor/bin/sail npm install
   ```

5. **Gerar a Chave da Aplicação**:
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

6. **Executar as Migrações e Seeders**:
   ```bash
   ./vendor/bin/sail artisan migrate --seed
   ```

7. **Compilar os Assets de Frontend**:
   ```bash
   ./vendor/bin/sail npm run dev
   ```

8. **Acessar a Aplicação**:
   Abra o seu navegador em [http://localhost](http://localhost).

---

## 🧪 Executando os Testes

O projeto utiliza o **Pest PHP** para testes automatizados unitários e de funcionalidade:

```bash
./vendor/bin/sail artisan test --compact
```

Para validar a formatação do código no padrão Laravel Pint:

```bash
./vendor/bin/sail bin pint --test
```

---

## 🤝 Como Contribuir

Contribuições são extremamente bem-vindas! Seja corrigindo um bug, propondo melhorias na documentação ou criando novas funcionalidades.

1. Faça um **Fork** do projeto.
2. Crie uma **Branch** para a sua funcionalidade (`git checkout -b feature/minha-nova-funcionalidade`).
3. Siga o padrão de **Conventional Commits** em português (ex: `feat(devocional): adiciona filtro por tema`).
4. Envie seus commits (`git commit -m 'feat(devocional): adiciona filtro por tema'`).
5. Faça o Push da sua Branch (`git push origin feature/minha-nova-funcionalidade`).
6. Abra um **Pull Request**.

Por favor, certifique-se de ler o nosso [Código de Conduta](CODE_OF_CONDUCT.md) antes de contribuir.

---

## 📄 Licença

Este projeto é de código aberto e está licenciado sob a [Licença MIT](LICENSE).

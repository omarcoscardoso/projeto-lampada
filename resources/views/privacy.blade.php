<!DOCTYPE html>
<html class="scroll-smooth" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Política de Privacidade - {{ config('app.name', 'Projeto Lâmpada') }}</title>
    <meta name="description" content="Política de Privacidade do Projeto Lâmpada. Saiba como protegemos seus dados.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-800 font-sans selection:bg-amber-500/10 selection:text-amber-600 antialiased">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100">
        <nav aria-label="Global" class="flex items-center justify-between p-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex lg:flex-1">
                <a href="{{ route('landing') }}" class="-m-1.5 p-1.5 flex items-center gap-2 transition-transform hover:scale-105">
                    <img src="https://storage.googleapis.com/iprviamao-com-br/lampada/logo_lampada_app.webp" alt="Logo Lâmpada" class="h-10 w-auto" />
                </a>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('landing') }}" class="text-sm font-bold text-slate-700 hover:text-amber-600 transition-colors">
                    &larr; Voltar ao Início
                </a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="py-16 px-6 md:px-12 max-w-4xl mx-auto">
        <div class="bg-white rounded-3xl p-8 md:p-14 shadow-xl shadow-slate-200/50 border border-slate-100">
            <!-- Header Section -->
            <div class="border-b border-slate-100 pb-8 mb-10">
                <span class="inline-block px-3.5 py-1.5 rounded-full bg-amber-50 text-amber-700 font-bold text-xs uppercase tracking-wider mb-4 border border-amber-200/50">
                    Transparência &amp; Segurança
                </span>
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-4">
                    Política de Privacidade
                </h1>
                <p class="text-slate-500 text-sm font-medium">
                    Última atualização: {{ date('d/m/Y') }} &bull; Projeto Lâmpada
                </p>
            </div>

            <!-- Content Sections -->
            <div class="space-y-10 text-slate-600 leading-relaxed text-base sm:text-lg">
                <!-- 1. Introdução -->
                <section>
                    <h2 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">1. Introdução e Compromisso</h2>
                    <p class="mb-3">
                        A <strong>Igreja Presbiteriana Renovada de Viamão</strong>, através da plataforma <strong>Projeto Lâmpada</strong>, preza pela privacidade, segurança e transparência no tratamento dos dados pessoais de nossos membros, participantes e visitantes.
                    </p>
                    <p>
                        Esta Política de Privacidade descreve como coletamos, usamos, armazenamos e protegemos suas informações pessoais ao utilizar nosso aplicativo web e serviços associados.
                    </p>
                </section>

                <!-- 2. Informações que Coletamos -->
                <section>
                    <h2 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">2. Informações que Coletamos</h2>
                    <p class="mb-4">Para proporcionar uma experiência personalizada e contínua de leitura bíblica devocional, coletamos as seguintes categorias de informações:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li><strong>Dados de Autenticação (Google OAuth):</strong> Nome completo, endereço de e-mail e foto de perfil fornecidos via Login com o Google. Não armazenamos senhas de sua conta Google.</li>
                        <li><strong>Dados de Uso e Leitura:</strong> Registros das leituras diárias concluídas, datas de acesso, histórico de navegação interna e preferências de configurações de voz (TTS) ou rolagem.</li>
                        <li><strong>Interações com o Assistente IA (Lampião):</strong> Dúvidas teológicas, consultas e mensagens enviadas ao assistente virtual Lampião, utilizadas para gerar respostas contextualizadas.</li>
                        <li><strong>Informações Técnicas:</strong> Endereço IP, tipo de navegador, sistema operacional e dados básicos de diagnóstico para garantia de segurança e bom funcionamento da plataforma.</li>
                    </ul>
                </section>

                <!-- 3. Como Utilizamos Suas Informações -->
                <section>
                    <h2 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">3. Como Utilizamos Suas Informações</h2>
                    <p class="mb-4">Os dados coletados têm finalidades estritamente ligadas à prestação do serviço espiritual e comunitário do Projeto Lâmpada:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Identificar você e manter seu progresso de leitura bíblica sincronizado em todos os seus dispositivos.</li>
                        <li>Fornecer respostas teológicas precisas e contextualizadas através do assistente virtual Lampião.</li>
                        <li>Garantir a segurança da sua conta e prevenir acessos não autorizados.</li>
                        <li>Melhorar continuamente a usabilidade e desempenho da aplicação.</li>
                    </ul>
                </section>

                <!-- 4. Compartilhamento e Proteção de Dados -->
                <section>
                    <h2 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">4. Compartilhamento e Proteção de Dados</h2>
                    <p class="mb-3">
                        <strong>Não vendemos, alugamos ou comercializamos seus dados pessoais em nenhuma hipótese.</strong>
                    </p>
                    <p class="mb-3">
                        Seus dados poderão ser compartilhados apenas com provedores de infraestrutura estritamente necessários para o funcionamento do aplicativo (como serviços de hospedagem em nuvem e APIs de inteligência artificial), sob dever de confidencialidade e altos padrões de segurança.
                    </p>
                    <p>
                        Adotamos medidas técnicas e organizacionais adequadas (criptografia HTTPS/TLS, controles de acesso restrito e bancos de dados seguros) para proteger suas informações contra perdas, acessos não autorizados e alterações.
                    </p>
                </section>

                <!-- 5. Serviços de Terceiros -->
                <section>
                    <h2 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">5. Serviços de Terceiros</h2>
                    <p class="mb-3">O aplicativo utiliza os seguintes serviços de terceiros de confiança:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li><strong>Google OAuth:</strong> Utilizado para autenticação segura e simplificada. Sujeito à Política de Privacidade do Google.</li>
                        <li><strong>Google Cloud Platform &amp; Laravel AI SDK:</strong> Utilizados para processamento seguro do assistente teológico Lampião e armazenamento da aplicação.</li>
                    </ul>
                </section>

                <!-- 6. Direitos do Usuário (LGPD) -->
                <section>
                    <h2 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">6. Seus Direitos (LGPD)</h2>
                    <p class="mb-4">Em conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018 - LGPD), você possui os seguintes direitos:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li><strong>Acesso e Confirmação:</strong> Solicitar a confirmação da existência de tratamento de seus dados pessoais e acesso a eles.</li>
                        <li><strong>Correção:</strong> Solicitar a correção de dados incompletos, inexatos ou desatualizados.</li>
                        <li><strong>Exclusão / Anonymização:</strong> Solicitar a exclusão definitiva ou anonimização da sua conta e histórico de dados associados.</li>
                        <li><strong>Revogação de Consentimento:</strong> Desconectar o login do Google ou solicitar o encerramento da conta a qualquer momento.</li>
                    </ul>
                </section>

                <!-- 7. Alterações nesta Política -->
                <section>
                    <h2 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">7. Alterações nesta Política</h2>
                    <p>
                        Esta Política de Privacidade poderá ser atualizada periodicamente para refletir melhorias no aplicativo ou mudanças normativas. Recomendamos a consulta regular desta página. Notificaremos os usuários em caso de alterações significativas.
                    </p>
                </section>

                <!-- 8. Contato -->
                <section class="bg-amber-50/60 rounded-2xl p-6 border border-amber-200/50">
                    <h2 class="text-xl font-black text-slate-900 mb-2 tracking-tight">8. Contato e Encarregado de Privacidade</h2>
                    <p class="text-slate-700 text-base">
                        Para dúvidas, exercício de direitos ou solicitações referentes à sua privacidade e dados pessoais, entre em contato conosco através do e-mail oficial da Igreja Presbiteriana Renovada de Viamão:
                    </p>
                    <p class="mt-3 font-bold text-amber-700">
                        <a href="mailto:cardoso.oliveira@gmail.com" class="underline hover:text-amber-800">cardoso.oliveira@gmail.com</a>
                    </p>
                </section>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-100 py-12 px-6">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6 text-sm text-slate-500 font-medium">
            <div class="flex items-center gap-2">
                <img src="https://storage.googleapis.com/iprviamao-com-br/lampada/logo_lampada_app.webp" alt="Logo Lâmpada" class="h-6 w-auto opacity-70" />
                <span>&copy; {{ date('Y') }} Projeto Lâmpada.</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="{{ route('privacy') }}" class="text-amber-600 font-bold hover:underline">Política de Privacidade</a>
                <a href="{{ route('terms') }}" class="hover:text-slate-900 transition-colors">Termos de Serviço</a>
                <a href="{{ route('landing') }}" class="hover:text-slate-900 transition-colors">Início</a>
            </div>
        </div>
    </footer>
</body>

</html>

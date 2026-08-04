<!DOCTYPE html>
<html class="scroll-smooth" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Termos de Serviço - {{ config('app.name', 'Projeto Lâmpada') }}</title>
    <meta name="description" content="Termos de Serviço do Projeto Lâmpada - Igreja Presbiteriana Renovada de Viamão. Condições de uso da plataforma.">

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
                    Condições de Uso
                </span>
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-4">
                    Termos de Serviço
                </h1>
                <p class="text-slate-500 text-sm font-medium">
                    Última atualização: {{ date('d/m/Y') }} &bull; Projeto Lâmpada (Igreja Presbiteriana Renovada de Viamão)
                </p>
            </div>

            <!-- Content Sections -->
            <div class="space-y-10 text-slate-600 leading-relaxed text-base sm:text-lg">
                <!-- 1. Aceitação dos Termos -->
                <section>
                    <h2 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">1. Aceitação dos Termos</h2>
                    <p class="mb-3">
                        Ao acessar ou utilizar a plataforma <strong>Projeto Lâmpada</strong>, mantida pela <strong>Igreja Presbiteriana Renovada de Viamão</strong>, você concorda em cumprir e vincular-se a estes Termos de Serviço e a todas as leis e regulamentos aplicáveis.
                    </p>
                    <p>
                        Caso não concorde com qualquer disposição destes termos, orientamos que descontinue o uso do aplicativo.
                    </p>
                </section>

                <!-- 2. Descrição do Serviço -->
                <section>
                    <h2 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">2. Descrição do Serviço e Propósito</h2>
                    <p class="mb-3">
                        O Projeto Lâmpada é uma ferramenta digital voltada para a edificação cristã, disponibilizando um plano de leitura bíblica diário em paralelo (Antigo e Novo Testamento), recursos de áudio/leitura, comentários devocionais e um assistente teológico interativo denominado <strong>Lampião</strong>.
                    </p>
                    <p>
                        O serviço tem caráter estritamente edificante, educacional e comunitário, visando apoiar a disciplina de leitura das Sagradas Escrituras.
                    </p>
                </section>

                <!-- 3. Conta de Usuário e Autenticação -->
                <section>
                    <h2 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">3. Conta de Usuário e Autenticação</h2>
                    <p class="mb-4">Para acessar as funcionalidades completas de acompanhamento de leitura e interação com o assistente IA, o usuário deve autenticar-se através de sua conta do Google.</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Você é responsável por manter a confidencialidade e a segurança das suas credenciais de acesso do Google.</li>
                        <li>Você concorda em notificar imediatamente a equipe do aplicativo sobre qualquer uso não autorizado da sua conta.</li>
                        <li>O aplicativo reserva-se o direito de suspender ou encerrar contas que violem estes termos de serviço ou apresentem comportamento malicioso.</li>
                    </ul>
                </section>

                <!-- 4. Conduta e Uso Aceitável -->
                <section>
                    <h2 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">4. Conduta do Usuário e Uso Aceitável</h2>
                    <p class="mb-4">Ao utilizar a plataforma, você concorda em:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Não utilizar o aplicativo para fins ilícitos, difamatórios, abusivos ou em desconformidade com a ética cristã.</li>
                        <li>Não tentar violar a segurança do aplicativo, sobrecarregar os servidores ou realizar engenharia reversa nos componentes da plataforma.</li>
                        <li>Não enviar conteúdos nocivos, automações não autorizadas ou comandos de mal uso para o assistente de inteligência artificial Lampião.</li>
                    </ul>
                </section>

                <!-- 5. Propriedade Intelectual -->
                <section>
                    <h2 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">5. Propriedade Intelectual</h2>
                    <p class="mb-3">
                        Todos os elementos visuais, código-fonte, marcas, logotipos, comentários devocionais e estrutura do Projeto Lâmpada são de propriedade exclusiva da Igreja Presbiteriana Renovada de Viamão ou de seus respectivos licenciantes.
                    </p>
                    <p>
                        Os textos bíblicos reproduzidos na plataforma são protegidos por direitos autorais e utilizados em conformidade com as devidas licenças ou domínio público aplicável.
                    </p>
                </section>

                <!-- 6. Assistente de Inteligência Artificial (Lampião) -->
                <section>
                    <h2 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">6. Assistente de Inteligência Artificial (Lampião)</h2>
                    <p class="mb-3">
                        O assistente <strong>Lampião</strong> utiliza modelos de inteligência artificial para auxiliar na compreensão dos textos bíblicos e tirar dúvidas teológicas.
                    </p>
                    <p>
                        Embora o Lampião seja configurado com diretrizes teológicas bíblicas, as respostas geradas por IA têm caráter educativo e consultivo. Recomendamos sempre a leitura direta da Bíblia e o acompanhamento pastoral junto à sua comunidade local.
                    </p>
                </section>

                <!-- 7. Modificações no Serviço e Termos -->
                <section>
                    <h2 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">7. Modificações no Serviço e nos Termos</h2>
                    <p>
                        A Igreja Presbiteriana Renovada de Viamão reserva-se o direito de atualizar, modificar ou descontinuar recursos do aplicativo a qualquer momento, bem como alterar estes Termos de Serviço. As alterações entrarão em vigor a partir da data de sua publicação nesta página.
                    </p>
                </section>

                <!-- 8. Isenção de Garantias -->
                <section>
                    <h2 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">8. Isenção de Garantias</h2>
                    <p>
                        O aplicativo é fornecido "como está" e "conforme disponível". Esforçamo-nos para garantir alta disponibilidade e precisão dos conteúdos, contudo não garantimos que a plataforma estará isenta de interrupções temporárias decorrentes de manutenção ou falhas de terceiros.
                    </p>
                </section>

                <!-- 9. Legislação Aplicável e Contato -->
                <section class="bg-amber-50/60 rounded-2xl p-6 border border-amber-200/50">
                    <h2 class="text-xl font-black text-slate-900 mb-2 tracking-tight">9. Legislação Aplicável e Contato</h2>
                    <p class="text-slate-700 text-base mb-3">
                        Estes Termos de Serviço são regidos pelas leis da República Federativa do Brasil. Para eventuais esclarecimentos sobre estes termos, entre em contato:
                    </p>
                    <p class="font-bold text-amber-700">
                        Igreja Presbiteriana Renovada de Viamão<br>
                        <a href="mailto:contato@iprviamao.com.br" class="underline hover:text-amber-800">contato@iprviamao.com.br</a>
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
                <span>&copy; {{ date('Y') }} Igreja Presbiteriana Renovada de Viamão.</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="{{ route('privacy') }}" class="hover:text-slate-900 transition-colors">Política de Privacidade</a>
                <a href="{{ route('terms') }}" class="text-amber-600 font-bold hover:underline">Termos de Serviço</a>
                <a href="{{ route('landing') }}" class="hover:text-slate-900 transition-colors">Início</a>
            </div>
        </div>
    </footer>
</body>

</html>

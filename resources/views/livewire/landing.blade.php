<div class="bg-white min-h-screen">
    <!-- Header -->
    <header class="absolute fixed shadow-sm bg-white/50 backdrop-blur-md border-b border-slate-100 inset-x-0 top-0 z-50">
        <nav aria-label="Global" class="flex items-center justify-between p-6 lg:px-8 max-w-7xl mx-auto">
            <div class="flex lg:flex-1">
                <a href="/" class="-m-1.5 p-1.5 flex items-center gap-2 transition-transform hover:scale-105">
                    <img src="https://storage.googleapis.com/iprviamao-com-br/lampada/logo_lampada_app.webp" alt="Logo Lâmpada" class="h-10 w-auto" />
                </a>
            </div>
            <div class="lg:flex lg:flex-1 lg:justify-end">
                @auth
                    <a href="{{ route('app') }}" class="text-sm font-black text-slate-900 flex items-center gap-2 hover:text-amber-600 transition-colors group">
                        Ir para o App <span aria-hidden="true" class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </a>
                @else
                    <a href="{{ route('auth.google.redirect') }}" class="text-sm font-black text-slate-900 flex items-center gap-2 hover:text-amber-600 transition-colors group">
                        Entrar <span aria-hidden="true" class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    <!-- 1. Hero Section -->
    <section id="lampada-hero" class="relative pt-48 pb-24 sm:pt-56 sm:pb-32 px-6 md:px-12 bg-slate-50 text-center overflow-hidden">
        <!-- Background Blur -->
        <div aria-hidden="true" class="absolute inset-x-0 -top-40 -z-0 transform-gpu overflow-hidden blur-3xl sm:-top-80">
            <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-amber-200 to-amber-500 opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"></div>
        </div>

        <!-- Botão de Compartilhar Flutuante -->
        <button @click="shareLampada()" class="fixed top-24 right-6 md:top-28 md:right-12 bg-white p-4 rounded-full shadow-2xl text-slate-900 hover:scale-110 hover:bg-slate-50 transition-all group z-40 border border-slate-100" aria-label="Compartilhar página" title="Compartilhar">
            <x-lucide-share-2 class="w-6 h-6 text-slate-900 group-hover:text-amber-600 transition-colors" />
        </button>

        <div class="max-w-4xl mx-auto relative z-10">
            <img src="https://storage.googleapis.com/iprviamao-com-br/images_site/logo_lampada.webp" alt="Logo do Projeto Lâmpada" class="w-40 sm:w-48 mx-auto mb-8 animate-fade-in-up">
            <h1 class="text-5xl sm:text-6xl md:text-7xl font-black text-slate-900 tracking-tighter mb-6 animate-fade-in-up">
                Projeto <span class="italic font-serif text-amber-600 underline decoration-amber-600/20 underline-offset-8">Lâmpada</span>
            </h1>
            <h2 class="text-2xl sm:text-3xl md:text-3xl font-black text-slate-900 tracking-tighter mb-4 animate-fade-in-up" style="animation-delay: 100ms">Toda a Escritura para Toda a Vida.</h2>
            <div class="max-w-3xl mx-auto animate-fade-in-up" style="animation-delay: 200ms">
                <p class="text-slate-500 mb-6 text-xl italic">"Lâmpada para os meus pés é tua palavra e luz, para o meu caminho" (Salmo 119:105).</p>
                <p class="text-slate-600 mb-12 text-xl leading-relaxed">Una-se a nós na jornada de ler a Bíblia completa em um ano, com foco na compreensão profunda e no crescimento espiritual.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-6">
                    @auth
                        <a href="{{ route('app') }}" class="rounded-full bg-amber-600 px-12 py-5 text-xl font-black text-white shadow-2xl shadow-amber-600/30 hover:bg-amber-500 transition-all hover:scale-105 active:scale-95 text-center">Ir para o App</a>
                    @else
                        <a href="{{ route('auth.google.redirect') }}" class="rounded-full bg-amber-600 px-12 py-5 text-xl font-black text-white shadow-2xl shadow-amber-600/30 hover:bg-amber-500 transition-all hover:scale-105 active:scale-95 text-center">Começar Agora</a>
                    @endauth
                    <a href="#oq-e" class="rounded-full bg-white border-2 border-slate-200 px-12 py-5 text-xl font-black text-slate-900 hover:bg-slate-50 transition-all hover:scale-105 active:scale-95 text-center">Saiba Mais</a>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. O que é o Projeto Lâmpada? -->
    <section id="oq-e" class="py-20 sm:py-32 px-6 md:px-12 bg-white border-y border-slate-50 overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <!-- Left: App Mockup -->
                <div class="relative order-2 lg:order-1 animate-fade-in-up">
                    <div class="absolute -top-10 -left-10 w-40 h-40 bg-amber-500/10 rounded-full blur-3xl -z-10"></div>
                    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl -z-10"></div>
                    <img src="https://storage.googleapis.com/projetolampada/img/fone_lampada_001.jpg" alt="App Lâmpada no Celular" class="w-65 max-w-sm mx-auto rounded-[3rem] shadow-2xl border-8 border-slate-900 transform lg:-rotate-6 hover:rotate-0 transition-transform duration-500">
                </div>

                <!-- Right: Content -->
                <div class="text-center lg:text-left space-y-8 order-1 lg:order-2">
                    <div class="w-16 h-16 bg-slate-900/5 rounded-2xl flex items-center justify-center mx-auto lg:mx-0">
                        <x-lucide-help-circle class="text-slate-900 w-8 h-8" />
                    </div>
                    <h2 class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tighter leading-tight">O que é o <span class="text-amber-600">Projeto Lâmpada?</span></h2>
                    <p class="text-slate-600 text-xl leading-relaxed font-medium">
                        O Projeto Lâmpada é uma iniciativa de leitura bíblica dirigida, fundamentada no princípio de que a Bíblia é a autoridade suprema e suficiente para a vida do cristão.
                    </p>
                    <p class="text-slate-600 text-xl leading-relaxed font-medium">
                        Nosio objetivo não é apenas o cumprimento de uma meta de leitura, mas a formação de uma mente bíblica e um coração devoto através da exposição constante à Palavra de Deus, do Gênesis ao Apocalipse.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Para quem é? -->
    <section id="para-quem" class="py-20 sm:py-24 px-6 md:px-12 bg-slate-50">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tighter mb-4">Para quem é o projeto?</h2>
                <p class="text-slate-500 max-w-2xl mx-auto text-xl font-medium">Se você se identifica com um destes perfis, esta jornada é para você.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/50 text-center hover:border-amber-500/30 transition-all">
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <x-lucide-repeat class="w-7 h-7" />
                    </div>
                    <h3 class="font-black text-slate-900 text-2xl mb-4">Para quem deseja constância</h3>
                    <p class="text-slate-500 text-lg leading-relaxed font-medium">Ideal para quem tem dificuldade em manter uma disciplina de leitura individual.</p>
                </div>
                <!-- Card 2 -->
                <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/50 text-center hover:border-amber-500/30 transition-all">
                    <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <x-lucide-book-open-check class="w-7 h-7" />
                    </div>
                    <h3 class="font-black text-slate-900 text-2xl mb-4">Para quem busca compreensão</h3>
                    <p class="text-slate-500 text-lg leading-relaxed font-medium">Para cristãos que desejam entender como o Antigo e o Novo Testamento se conectam.</p>
                </div>
                <!-- Card 3 -->
                <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/50 text-center hover:border-amber-500/30 transition-all">
                    <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <x-lucide-user-plus class="w-7 h-7" />
                    </div>
                    <h3 class="font-black text-slate-900 text-2xl mb-4">Para novos e antigos na fé</h3>
                    <p class="text-slate-500 text-lg leading-relaxed font-medium">Seja para ler pela primeira vez ou para redescobrir as riquezas das Escrituras.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Como funciona? -->
    <section id="metodo" class="py-20 sm:py-24 px-6 md:px-12 bg-white">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div>
                    <span class="text-amber-600 font-black tracking-[0.2em] text-xs uppercase mb-6 block border-l-4 border-amber-600 pl-4">O Método</span>
                    <h2 class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tighter mb-8 leading-tight">Leitura em Paralelo</h2>
                    <p class="text-slate-600 text-xl leading-relaxed mb-8 font-medium">Diferente de leituras puramente cronológicas que podem se tornar exaustivas, utilizamos o Método de Leitura em Paralelo para manter a jornada dinâmica e reveladora.</p>
                </div>
                <div class="space-y-8">
                    <div class="flex items-start gap-6 group">
                        <div class="w-14 h-14 bg-slate-50 rounded-2xl flex-shrink-0 flex items-center justify-center group-hover:bg-amber-50 transition-colors">
                            <x-lucide-book-copy class="w-7 h-7 text-slate-900 group-hover:text-amber-600 transition-colors" />
                        </div>
                        <div>
                            <h4 class="font-black text-slate-900 text-xl mb-2 tracking-tight">Dose Diária Dupla</h4>
                            <p class="text-slate-500 text-lg leading-relaxed font-medium">Todos os dias, você receberá referências do Antigo e do Novo Testamento, vendo as promessas e seu cumprimento em Cristo simultaneamente.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-6 group">
                        <div class="w-14 h-14 bg-slate-50 rounded-2xl flex-shrink-0 flex items-center justify-center group-hover:bg-amber-50 transition-colors">
                            <x-lucide-smartphone class="w-7 h-7 text-slate-900 group-hover:text-amber-600 transition-colors" />
                        </div>
                        <div>
                            <h4 class="font-black text-slate-900 text-xl mb-2 tracking-tight">Comunidade no WhatsApp</h4>
                            <p class="text-slate-500 text-lg leading-relaxed font-medium">Você fará parte de um grupo focado e silencioso, onde receberá o guia de leitura e breves explicações exegéticas.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-6 group">
                        <div class="w-14 h-14 bg-slate-50 rounded-2xl flex-shrink-0 flex items-center justify-center group-hover:bg-amber-50 transition-colors">
                            <x-lucide-calendar-check class="w-7 h-7 text-slate-900 group-hover:text-amber-600 transition-colors" />
                        </div>
                        <div>
                            <h4 class="font-black text-slate-900 text-xl mb-2 tracking-tight">Acompanhamento Constante</h4>
                            <p class="text-slate-500 text-lg leading-relaxed font-medium">A jornada dura 365 dias, mas o suporte é diário. Você nunca lerá sozinho.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4.1. Conheça o Lampião: Sua IA Mentora -->
    <section id="lampiao" class="py-20 sm:py-32 px-6 md:px-12 bg-slate-50 overflow-hidden border-t border-slate-100">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <!-- Left: Content -->
                <div class="text-center lg:text-left space-y-8 animate-fade-in-up">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-600/10 text-amber-600 font-black text-xs uppercase tracking-widest mx-auto lg:mx-0">
                        <x-lucide-siren class="size-4" />
                        Exclusivo
                    </div>
                    <h2 class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tighter leading-tight">Conheça o <span class="text-amber-600">Lampião</span>: Seu Mentor Bíblico com IA</h2>
                    <p class="text-slate-600 text-xl leading-relaxed font-medium">
                        Imagine ter um guia teológico ao seu lado durante cada leitura. O Lampião é o assistente inteligente do projeto, treinado especificamente no contexto de cada capítulo que você lê.
                    </p>
                    <p class="text-slate-600 text-xl leading-relaxed font-medium">
                        Tem dúvidas sobre um termo no original, o contexto histórico do Antigo Testamento ou como uma promessa se cumpre em Cristo? O Lampião está pronto para iluminar seu caminho com explicações profundas e fundamentadas, 24 horas por dia.
                    </p>
                </div>

                <!-- Right: AI Mockup -->
                <div class="relative animate-fade-in-up" style="animation-delay: 200ms">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-amber-500/10 rounded-full blur-3xl -z-10"></div>
                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl -z-10"></div>
                    <img src="https://storage.googleapis.com/projetolampada/img/fone_lampada_002.jpg" alt="Lampião AI no Celular" class="w-65 max-w-sm mx-auto rounded-[3rem] shadow-2xl border-8 border-slate-900 transform lg:rotate-6 hover:rotate-0 transition-transform duration-500">
                </div>
            </div>
        </div>
    </section>

    <!-- 5. Por que participar? -->
    <section id="por-que-participar" class="py-20 sm:py-24 px-6 md:px-12 bg-slate-50">
        <div class="max-w-4xl mx-auto text-center">
            <div class="w-16 h-16 bg-amber-600/10 rounded-2xl flex items-center justify-center mx-auto mb-8">
                <x-lucide-cross class="text-amber-600 w-8 h-8" />
            </div>
            <h2 class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tighter mb-6">Por que participar?</h2>
            <p class="text-slate-500 mb-10 text-xl max-w-2xl mx-auto font-medium">Nossa motivação é o crescimento na fé, fundamentado na Palavra.</p>
            <div class="space-y-4 text-left max-w-2xl mx-auto">
                <div class="bg-white p-8 rounded-[2rem] border-2 border-amber-600/10 hover:border-amber-600 transition-all shadow-sm">
                    <h4 class="font-black text-slate-900 text-xl mb-3 tracking-tight">Crescimento na Graça</h4>
                    <p class="text-slate-600 text-lg font-medium">A fé vem pelo ouvir, e o ouvir pela Palavra de Cristo (Romanos 10:17).</p>
                </div>
                <div class="bg-white p-8 rounded-[2rem] border-2 border-amber-600/10 hover:border-amber-600 transition-all shadow-sm">
                    <h4 class="font-black text-slate-900 text-xl mb-3 tracking-tight">Discernimento Espiritual</h4>
                    <p class="text-slate-600 text-lg font-medium">Em tempos de confusão doutrinária, conhecer toda a Escritura é a nossa única salvaguarda.</p>
                </div>
                <div class="bg-white p-8 rounded-[2rem] border-2 border-amber-600/10 hover:border-amber-600 transition-all shadow-sm">
                    <h4 class="font-black text-slate-900 text-xl mb-3 tracking-tight">Comunhão</h4>
                    <p class="text-slate-600 text-lg font-medium">Crescemos mais quando crescemos juntos. O grupo serve como um lembrete e um encorajamento mútuo (Hebreus 10:24-25).</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. Chamada para Ação (CTA) -->
    <section id="cta-final" class="py-20 sm:py-24 px-6 md:px-12 bg-blue-900 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-blue-800 mix-blend-multiply opacity-50 -z-0"></div>
        <div class="max-w-3xl mx-auto text-center relative z-10">
            <h2 class="text-5xl sm:text-6xl font-black tracking-tighter mb-8 leading-tight">Pronto para começar?</h2>
            <p class="text-blue-100/90 mb-12 text-xl font-medium leading-relaxed">Comece hoje sua jornada de amadurecimento espiritual com a Palavra.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                @auth
                    <a href="{{ route('app') }}" class="w-full sm:w-auto bg-amber-500 text-slate-900 px-12 py-6 rounded-2xl font-black flex items-center justify-center gap-3 transition-all hover:scale-105 hover:bg-amber-400 shadow-2xl shadow-amber-500/20 text-xl">
                        Ir para o App
                    </a>
                @else
                    <a href="{{ route('auth.google.redirect') }}" class="w-full sm:w-auto bg-amber-500 text-slate-900 px-12 py-6 rounded-2xl font-black flex items-center justify-center gap-3 transition-all hover:scale-105 hover:bg-amber-400 shadow-2xl shadow-amber-500/20 text-xl">
                        Entrar Agora
                    </a>
                @endauth
                <a href="https://chat.whatsapp.com/KX8S51kTBg4Klc7Lc9gmCE" class="w-full sm:w-auto bg-green-500 text-slate-900 px-12 py-6 rounded-2xl font-black flex items-center justify-center gap-3 transition-all hover:scale-105 hover:bg-green-400 shadow-2xl shadow-amber-500/20 text-xl" target="_blank">
                    <x-lucide-message-circle class="w-6 h-6 text-white" />
                    Grupo de leitura
                </a>
            </div>
        </div>
    </section>
</div>
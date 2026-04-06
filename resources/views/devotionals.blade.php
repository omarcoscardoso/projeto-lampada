<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1e3a5f">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Lâmpada">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">

    <title>Leitura Bíblica Diária - {{ config('app.name', 'Lâmpada') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Vanilla Calendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/build/vanilla-calendar.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/build/themes/light.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/build/themes/dark.min.css" rel="stylesheet">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js via CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .vc-container {
            width: 100% !important;
            border: none !important;
            background: transparent !important;
        }

        .vc-header {
            background: transparent !important;
        }

        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom);
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Correção para o calendário não vazar na sidebar */
        #calendar-sidebar {
            font-size: 0.7rem !important;
            width: 100% !important;
            max-width: 255px !important;
            margin: 0 auto !important;
        }

        #calendar-sidebar .vc-day__content {
            width: 30px !important;
            height: 30px !important;
            line-height: 30px !important;
        }

        #calendar-sidebar .vc-header {
            padding: 0.5rem !important;
        }
    </style>
</head>

<body class="bg-white dark:bg-black font-sans antialiased text-slate-900 dark:text-slate-100 h-full overflow-hidden"
    x-data="{ 
        showCalendar: false, 
        showAiChat: false,
        showBibleModal: false,
        messages: [],
        userInput: '',
        isAiLoading: false,
        bibleLoading: false,
        bibleData: null,
        
        async sendToAi() {
            if (!this.userInput.trim() || this.isAiLoading) return;
            const userMsg = this.userInput;
            this.messages.push({ role: 'user', content: userMsg });
            this.userInput = '';
            this.isAiLoading = true;
            const contextElement = document.getElementById('devotional-content');
            const context = contextElement ? contextElement.innerText : '';
            this.$nextTick(() => { this.scrollToBottom(); });
            try {
                const response = await fetch('/api/ai/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content
                    },
                    body: JSON.stringify({
                        message: userMsg,
                        devotional_context: context,
                        history: this.messages.slice(0, -1)
                    })
                });
                const data = await response.json();
                if (data.response) {
                    this.messages.push({ role: 'ai', content: data.response });
                } else {
                    this.messages.push({ role: 'ai', content: data.error || 'Erro na resposta.' });
                }
            } catch (e) {
                this.messages.push({ role: 'ai', content: 'Não consegui me conectar agora.' });
            } finally {
                this.isAiLoading = false;
                this.$nextTick(() => { this.scrollToBottom(); });
            }
        },

        scrollToBottom() {
            // Rola tanto o container do modal quanto o da sidebar (se existir)
            const containers = document.querySelectorAll('[id^=chat-messages-container]');
            containers.forEach(c => c.scrollTo({ top: c.scrollHeight, behavior: 'smooth' }));
        },

        formatMarkdown(content) {
            if (!content) return '';
            let html = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
            html = html.replace(/^\s*[\-\*]\s+(.*)/gm, '• $1');
            return html;
        },

        isAutoScrolling: false,
        scrollPos: 0,

        toggleAutoScroll() {
            this.isAutoScrolling = !this.isAutoScrolling;
            if (this.isAutoScrolling) {
                const container = this.$refs.bibleContainer;
                if (!container) return;
                
                this.scrollPos = container.scrollTop;
                
                const scrollStep = () => {
                    if (!this.isAutoScrolling) return;
                    
                    this.scrollPos += 0.15; // Velocidade bem lenta
                    container.scrollTop = this.scrollPos;
                    
                    if (Math.ceil(this.scrollPos + container.clientHeight) >= container.scrollHeight - 10) {
                        this.isAutoScrolling = false;
                        return;
                    }
                    requestAnimationFrame(scrollStep);
                };
                requestAnimationFrame(scrollStep);
            }
        },

        async openBibleReader() {
            const refOld = document.getElementById('ref-old')?.innerText;
            const refNew = document.getElementById('ref-new')?.innerText;
            if (!refOld && !refNew) return;
            this.showBibleModal = true;
            this.isAutoScrolling = false; // Reset ao abrir
            this.bibleLoading = true;
            this.bibleData = null;
            try {
                const response = await fetch(`/api/bible/read?ref_old=${encodeURIComponent(refOld || '')}&ref_new=${encodeURIComponent(refNew || '')}`);
                this.bibleData = await response.json();
            } catch (e) {
                console.error('Erro ao buscar texto bíblico', e);
            } finally {
                this.bibleLoading = false;
            }
        }
    }"
    @close-calendar.window="showCalendar = false">

    <!-- Layout Wrapper -->
    <div class="flex h-screen w-full overflow-hidden">

        <!-- SIDEBAR ESQUERDA (Desktop - LG) -->
        <aside class="hidden lg:flex flex-col w-80 bg-slate-50 dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 shrink-0">
            <div class="p-6">
                <img src="https://storage.googleapis.com/iprviamao-com-br/lampada/logo_lampada_app.webp" alt="Logo Lâmpada" class="h-10 w-auto mb-10">

                <nav class="space-y-2">
                    <a href="/" class="flex items-center gap-3 px-4 py-3 bg-amber-500 text-white rounded-2xl shadow-lg shadow-amber-500/20 font-bold transition-all">
                        <x-lucide-home class="w-5 h-5" />
                        <span>Devocional</span>
                    </a>

                    <button @click="showAiChat = true" class="w-full flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl transition-all xl:hidden">
                        <x-lucide-siren class="w-5 h-5" />
                        <span>Lampião AI</span>
                    </button>

                    <a href="https://iprviamao.com.br/lampada/" target="_blank" class="flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl transition-all">
                        <x-lucide-info class="w-5 h-5" />
                        <span>Sobre</span>
                    </a>

                    <a href="/admin" class="flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl transition-all">
                        <x-lucide-layout-dashboard class="w-5 h-5" />
                        <span>Painel Admin</span>
                    </a>
                </nav>
            </div>

            <!-- Calendário Fixo na Sidebar -->
            <div class="mt-auto p-4">
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-2 shadow-sm border border-slate-100 dark:border-slate-700">
                    <div id="calendar-sidebar" class="vanilla-calendar light dark:dark !w-full"></div>
                </div>
                <p class="text-center text-[10px] text-slate-400 mt-4">Lâmpada APP &copy; 2026</p>
            </div>
        </aside>

        <!-- MAIN AREA (Content) -->
        <main class="flex-1 flex flex-col min-w-0 bg-white dark:bg-black relative overflow-y-auto scrollbar-hide pb-20 lg:pb-0">

            <!-- Mobile Header -->
            <header class="lg:hidden sticky top-0 w-full z-40 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between px-4 h-16">
                    <img src="https://storage.googleapis.com/iprviamao-com-br/lampada/logo_lampada_app.webp" alt="Logo Lâmpada" class="h-8 w-auto">
                    <button @click="showCalendar = true" class="p-2 text-amber-600 dark:text-amber-500 rounded-full bg-amber-50 dark:bg-amber-900/30">
                        <x-lucide-calendar class="w-6 h-6" />
                    </button>
                </div>
            </header>

            <!-- Container de Conteúdo (Max-width para leitura) -->
            <div class="mx-auto w-full max-w-4xl p-4 lg:p-10">
                <div id="devotional-display" class="relative">
                    <div id="devotional-card" class="bg-slate-50 dark:bg-slate-900/50 rounded-[40px] sm:rounded-[60px] border border-slate-100 dark:border-slate-800 p-6 sm:p-12 lg:p-16 relative">

                        <!-- Floating Actions (Bottom-Right Responsive) -->
                        <div class="absolute top-6 right-6 lg:top-10 lg:right-10 flex gap-2 z-20">
                            <button @click="openBibleReader" id="bible-read-btn" class="hidden items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-lg shadow-indigo-600/20 transition-all hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <x-lucide-book-open class="w-5 h-5" />
                                <span class="font-bold text-sm">Ler</span>
                            </button>
                            <button id="whatsapp-share-btn" class="hidden p-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-full shadow-lg shadow-emerald-500/20 transition-all hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                                <x-lucide-share-2 class="w-5 h-5" />
                            </button>
                        </div>

                        <div id="devotional-content" class="prose prose-slate dark:prose-invert max-w-none">
                            <!-- Skeleton ou Fallback -->
                            <div class="flex flex-col items-center justify-center py-20 text-center space-y-4">
                                <div class="p-4 bg-slate-100 dark:bg-slate-800 rounded-full">
                                    <x-lucide-book-open class="w-12 h-12 text-slate-300 animate-pulse" />
                                </div>
                                <p class="text-slate-500">Selecione uma data para começar seu devocional.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- SIDEBAR DIREITA (Desktop - XL - IA Chat Persistente) -->
        <aside class="hidden xl:flex flex-col w-[400px] bg-slate-50 dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shrink-0">
            <header class="h-20 flex items-center gap-4 px-6 border-b border-slate-200 dark:border-slate-800">
                <div class="p-2 bg-amber-100 dark:bg-amber-900/30 text-amber-600 rounded-xl">
                    <x-lucide-siren class="w-6 h-6" />
                </div>
                <div>
                    <h2 class="font-bold text-lg leading-tight">Assistente Lampião</h2>
                    <span class="text-xs text-emerald-500 font-medium">Online para ajudar</span>
                </div>
            </header>

            <div id="chat-messages-container-sidebar" class="flex-1 overflow-y-auto p-6 space-y-6 scrollbar-hide">
                <template x-if="messages.length === 0">
                    <div class="flex flex-col items-center justify-center h-full text-center p-8 space-y-4">
                        <div class="p-4 bg-white dark:bg-slate-800 rounded-3xl shadow-sm text-slate-400">
                            <p class="text-sm">Tire suas dúvidas sobre o devocional de hoje comigo.</p>
                        </div>
                    </div>
                </template>
                <template x-for="(msg, index) in messages" :key="index">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="msg.role === 'user' ? 'bg-amber-600 text-white rounded-2xl rounded-tr-none max-w-[90%] p-4 shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-2xl rounded-tl-none max-w-[90%] p-4 shadow-sm border border-slate-100 dark:border-slate-700'">
                            <div class="text-sm leading-relaxed whitespace-pre-line" x-html="msg.role === 'ai' ? formatMarkdown(msg.content) : msg.content"></div>
                        </div>
                    </div>
                </template>
                <div x-show="isAiLoading" class="flex justify-start">
                    <div class="bg-white dark:bg-slate-800 p-3 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 dark:border-slate-700">
                        <div class="flex gap-1">
                            <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce"></div>
                            <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                            <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.4s]"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
                <form @submit.prevent="sendToAi" class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800 rounded-2xl p-1">
                    <input x-model="userInput" type="text" placeholder="Pergunte algo..." class="flex-1 bg-transparent border-none px-4 py-3 text-sm focus:ring-0 focus:outline-none dark:text-white">
                    <button type="submit" :disabled="!userInput.trim() || isAiLoading" class="p-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl transition-colors disabled:opacity-50">
                        <x-lucide-send class="w-5 h-5" />
                    </button>
                </form>
            </div>
        </aside>

        <!-- MOBILE NAVIGATION (Fixed Bottom) -->
        <nav class="lg:hidden fixed bottom-0 left-0 w-full z-40 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 pb-safe">
            <div class="flex items-center justify-between h-16 px-4">
                <a href="/" class="flex flex-col items-center justify-center flex-1 text-amber-600 dark:text-amber-500">
                    <x-lucide-home class="w-6 h-6 mb-1" />
                    <span class="text-[10px] font-medium">Início</span>
                </a>
                <button @click="showAiChat = true" class="flex flex-col items-center justify-center flex-1 text-slate-400">
                    <x-lucide-siren class="w-6 h-6 mb-1" />
                    <span class="text-[10px] font-medium">Lampião</span>
                </button>
                <a href="https://iprviamao.com.br/lampada/" target="_blank" class="flex flex-col items-center justify-center flex-1 text-slate-400">
                    <x-lucide-info class="w-6 h-6 mb-1" />
                    <span class="text-[10px] font-medium">Sobre</span>
                </a>
                <a href="/admin" class="flex flex-col items-center justify-center flex-1 text-slate-400">
                    <x-lucide-layout-dashboard class="w-6 h-6 mb-1" />
                    <span class="text-[10px] font-medium">Admin</span>
                </a>
            </div>
        </nav>

    </div>

    <!-- MODALS (Mobile Context) -->

    <!-- Modal do Chat IA (Mobile/Tablet) -->
    <div x-show="showAiChat" class="xl:hidden fixed inset-0 z-[100] bg-slate-50 dark:bg-slate-950 flex flex-col"
        x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" x-cloak>
        <header class="h-16 flex items-center justify-between px-4 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shrink-0">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-100 dark:bg-amber-900/30 text-amber-600 rounded-xl">
                    <x-lucide-siren class="w-5 h-5" />
                </div>
                <h2 class="font-bold text-lg">Assistente Lampião</h2>
            </div>
            <button @click="showAiChat = false" class="p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full">
                <x-lucide-x class="w-6 h-6" />
            </button>
        </header>

        <div id="chat-messages-container-modal" class="flex-grow overflow-y-auto p-4 space-y-4 scrollbar-hide bg-slate-50 dark:bg-slate-950 pb-10">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user' ? 'bg-amber-600 text-white rounded-2xl rounded-tr-none max-w-[85%] p-3 shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-2xl rounded-tl-none max-w-[85%] p-3 shadow-sm border border-slate-100 dark:border-slate-700'">
                        <div class="text-sm leading-relaxed whitespace-pre-line" x-html="msg.role === 'ai' ? formatMarkdown(msg.content) : msg.content"></div>
                    </div>
                </div>
            </template>
            <div x-show="isAiLoading" class="flex justify-start">
                <div class="bg-white dark:bg-slate-800 p-3 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 dark:border-slate-700">
                    <div class="flex gap-1">
                        <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce"></div>
                        <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                        <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.4s]"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-1 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 pb-safe">
            <form @submit.prevent="sendToAi" class="p-4 flex items-center gap-2">
                <input x-model="userInput" type="text" placeholder="Tire suas dúvidas..." class="flex-grow bg-slate-100 dark:bg-slate-800 border-none rounded-2xl px-3 py-3 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none dark:text-white">
                <button type="submit" :disabled="!userInput.trim() || isAiLoading" class="p-3 bg-amber-600 hover:bg-amber-700 text-white rounded-2xl transition-colors disabled:opacity-50">
                    <x-lucide-send class="w-5 h-5" />
                </button>
            </form>
        </div>
    </div>

    <!-- Modal do Calendário (Mobile) -->
    <div x-show="showCalendar" class="fixed inset-0 z-50 flex items-center justify-center p-4 lg:hidden" x-cloak>
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" x-show="showCalendar" x-transition.opacity @click="showCalendar = false"></div>
        <div class="relative w-full max-w-sm bg-white dark:bg-slate-900 rounded-[40px] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700"
            x-show="showCalendar" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-lg font-bold">Selecione o Dia</h2>
                <button @click="showCalendar = false" class="p-2 text-slate-500 bg-slate-100 dark:bg-slate-800 rounded-full">
                    <x-lucide-x class="w-5 h-5" />
                </button>
            </div>
            <div class="p-4">
                <div id="calendar-modal" class="vanilla-calendar light dark:dark !w-full"></div>
            </div>
        </div>
    </div>

    <!-- Modal de Leitura Bíblica (Full Screen) -->
    <div x-show="showBibleModal" class="fixed inset-0 z-[110] flex flex-col bg-white dark:bg-slate-950"
        x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-300 transform" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full" x-cloak>

        <header class="h-16 lg:h-20 flex items-center justify-between px-4 lg:px-10 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shrink-0">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 rounded-xl">
                    <x-lucide-book-open class="w-5 h-5 lg:w-6 lg:h-6" />
                </div>
                <h2 class="font-bold text-lg lg:text-xl">Leitura Bíblica</h2>
            </div>

            <div class="flex items-center gap-2">
                <!-- Botão Auto-Scroll -->
                <button
                    @click="toggleAutoScroll"
                    :class="isAutoScrolling ? 'bg-amber-100 text-amber-600 border-amber-200' : 'bg-slate-100 text-slate-500 border-slate-200'"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl border transition-all hover:scale-105 active:scale-95">
                    <template x-if="isAutoScrolling">
                        <x-lucide-pause-circle class="w-5 h-5 animate-pulse" />
                    </template>
                    <template x-if="!isAutoScrolling">
                        <x-lucide-play-circle class="w-5 h-5" />
                    </template>
                    <span class="text-xs font-bold hidden sm:inline" x-text="isAutoScrolling ? 'Pausar Rolagem' : 'Auto Rolagem'"></span>
                </button>

                <button @click="showBibleModal = false" class="p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full">
                    <x-lucide-x class="w-6 h-6 lg:w-8 lg:h-8" />
                </button>
            </div>
        </header>

        <div x-ref="bibleContainer" class="flex-grow overflow-y-auto p-6 lg:p-20 scrollbar-hide pb-20">
            <div class="max-w-4xl mx-auto">
                <template x-if="bibleLoading">
                    <div class="flex flex-col items-center justify-center h-64 space-y-4">
                        <div class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                        <p class="text-slate-500 animate-pulse text-lg font-medium">Conectando à Bíblia Digital...</p>
                    </div>
                </template>

                <template x-if="!bibleLoading && bibleData">
                    <div class="space-y-16">
                        <!-- Antigo Testamento -->
                        <template x-if="bibleData.old_testament && bibleData.old_testament.success">
                            <div class="space-y-12">
                                <template x-for="chapter in bibleData.old_testament.chapters" :key="chapter.number">
                                    <section>
                                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white mb-8 border-l-8 border-indigo-500 pl-6" x-text="bibleData.old_testament.book_name + ' ' + chapter.number"></h3>
                                        <div class="space-y-6">
                                            <template x-for="verse in chapter.verses" :key="verse.number">
                                                <p class="text-xl lg:text-2xl leading-relaxed text-slate-700 dark:text-slate-300 font-serif">
                                                    <sup class="text-sm font-bold text-indigo-500 mr-2" x-text="verse.number"></sup>
                                                    <span x-text="verse.text"></span>
                                                </p>
                                            </template>
                                        </div>
                                    </section>
                                </template>
                            </div>
                        </template>

                        <!-- Novo Testamento -->
                        <template x-if="bibleData.new_testament && bibleData.new_testament.success">
                            <div class="space-y-12 border-t border-slate-200 dark:border-slate-800 pt-20">
                                <template x-for="chapter in bibleData.new_testament.chapters" :key="chapter.number">
                                    <section>
                                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white mb-8 border-l-8 border-rose-500 pl-6" x-text="bibleData.new_testament.book_name + ' ' + chapter.number"></h3>
                                        <div class="space-y-6">
                                            <template x-for="verse in chapter.verses" :key="verse.number">
                                                <p class="text-xl lg:text-2xl leading-relaxed text-slate-700 dark:text-slate-300 font-serif">
                                                    <sup class="text-sm font-bold text-rose-500 mr-2" x-text="verse.number"></sup>
                                                    <span x-text="verse.text"></span>
                                                </p>
                                            </template>
                                        </div>
                                    </section>
                                </template>
                            </div>
                        </template>

                        <template x-if="(!bibleData.old_testament || !bibleData.old_testament.success) && (!bibleData.new_testament || !bibleData.new_testament.success)">
                            <div class="text-center py-20 bg-slate-50 dark:bg-slate-900 rounded-[40px]">
                                <x-lucide-alert-circle class="w-16 h-16 text-slate-300 mx-auto mb-6" />
                                <p class="text-slate-500 text-xl">Não foi possível carregar o texto bíblico para estas referências.</p>
                            </div>
                        </template>

                        <!-- Botão de OK de Leitura -->
                        <div class="pt-20 pb-10 flex flex-col items-center">
                            <div class="w-20 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full mb-12"></div>
                            <button @click="const whatsappUrl = `https://wa.me/?text=${encodeURIComponent('Leitura do dia 🆗')}`; window.open(whatsappUrl, '_blank');"
                                class="flex items-center gap-3 px-12 py-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-3xl shadow-2xl shadow-emerald-600/30 transition-all hover:scale-105 active:scale-95 text-xl font-extrabold group">
                                <x-lucide-share-2 class="w-7 h-7 group-hover:rotate-12 transition-transform" />
                                Compartilhar OK de Leitura
                            </button>
                            <p class="mt-6 text-slate-400 dark:text-slate-500 text-base">Notifique seu grupo que você completou a leitura!</p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/build/vanilla-calendar.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Observer para fechar calendário no mobile após clique
            const observer = new MutationObserver((mutations) => {
                mutations.forEach(() => {
                    const calendars = ['#calendar-modal', '#calendar-sidebar'];
                    calendars.forEach(id => {
                        const el = document.querySelector(id);
                        if (el && !el.dataset.listenerAttached) {
                            el.dataset.listenerAttached = true;
                            el.addEventListener('click', (e) => {
                                if (e.target.closest('.vc-day')) {
                                    setTimeout(() => window.dispatchEvent(new CustomEvent('close-calendar')), 400);
                                }
                            });
                        }
                    });
                });
            });
            const body = document.querySelector('body');
            observer.observe(body, {
                childList: true,
                subtree: true
            });
        });
    </script>

    <!-- PWA Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(reg => console.log('[SW] OK')).catch(err => console.log('[SW] KO'));
            });
        }
    </script>
</body>

</html>
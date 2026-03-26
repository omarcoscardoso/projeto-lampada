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

    <title>Devocional Diário - {{ config('app.name', 'Lâmpada') }}</title>

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
    </style>
</head>

<body class="bg-slate-200 dark:bg-black font-sans antialiased text-slate-900 dark:text-slate-100 min-h-screen overflow-x-hidden">

    <!-- App Container -->
    <div
        class="mx-auto max-w-lg min-h-screen bg-slate-50 dark:bg-slate-950 relative shadow-2xl sm:border-x border-slate-300 dark:border-slate-800 flex flex-col"
        x-data="{ 
                showCalendar: false, 
                showAiChat: false,
                messages: [],
                userInput: '',
                isAiLoading: false,
                
                async sendToAi() {
                    if (!this.userInput.trim() || this.isAiLoading) return;
                    
                    const userMsg = this.userInput;
                    this.messages.push({ role: 'user', content: userMsg });
                    this.userInput = '';
                    this.isAiLoading = true;
                    
                    // Captura o contexto do devocional atual da tela
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
                                devotional_context: context
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
                    const container = document.getElementById('chat-messages-container');
                    if (container) {
                        container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
                    }
                }
            }"
        @close-calendar.window="showCalendar = false">

        <!-- Header Fixo -->
        <header class="fixed top-0 w-full max-w-lg z-40 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 transition-colors">
            <div class="flex items-center justify-between px-4 h-16">
                <div class="flex items-center">
                    <img src="https://storage.googleapis.com/iprviamao-com-br/images_site/logo_lampada_154x59.png" alt="Logo Lâmpada" class="h-10 w-auto">
                </div>

                <button
                    @click="showCalendar = true"
                    type="button"
                    class="p-2 rounded-full text-amber-600 dark:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/30 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <x-lucide-calendar class="w-6 h-6" />
                </button>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-grow pt-20 pb-24 px-4 overflow-y-auto">
            <div id="devotional-display" class="relative">
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden min-h-[500px] flex flex-col relative">

                    <button
                        id="whatsapp-share-btn"
                        class="hidden absolute top-6 right-6 p-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-full shadow-lg shadow-emerald-500/20 transition-all hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-emerald-400 z-20">
                        <x-lucide-share-2 class="w-5 h-5" />
                    </button>

                    <div id="devotional-content" class="p-6 sm:p-8 prose prose-slate dark:prose-invert max-w-none flex-grow">
                        <div class="flex flex-col items-center justify-center h-full py-20 text-center space-y-4">
                            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-full">
                                <x-lucide-book-open class="w-12 h-12 text-slate-300 animate-pulse" />
                            </div>
                            <p class="text-slate-500">Selecione uma data no calendário para ver o devocional.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Modal do Chat IA (Opção A: Tela Cheia com z-index SUPERIOR) -->
        <div
            x-show="showAiChat"
            class="fixed top-0 bottom-16 mx-auto w-full max-w-lg z-[100] bg-slate-50 dark:bg-slate-950 flex flex-col shadow-2xl"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-300 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            x-cloak>
            <!-- Chat Header -->
            <header class="h-16 flex items-center justify-between px-4 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-amber-100 dark:bg-amber-900/30 text-amber-600 rounded-xl">
                        <x-lucide-lamp class="w-5 h-5" />
                    </div>
                    <h2 class="font-bold text-lg">Assistente Lampião</h2>

                </div>
                <button @click="showAiChat = false" class="p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full">
                    <x-lucide-x class="w-6 h-6" />
                </button>
            </header>

            <!-- Chat Messages Area -->
            <div id="chat-messages-container" class="flex-grow overflow-y-auto p-4 space-y-4 scrollbar-hide bg-slate-50 dark:bg-slate-950 pb-10">
                <template x-if="messages.length === 0">
                    <div class="flex flex-col items-center justify-center h-full text-center p-8 space-y-4">
                        <div class="w-20 h-20 bg-amber-50 dark:bg-amber-900/20 rounded-full flex items-center justify-center text-amber-500">
                            <x-lucide-lamp class="w-10 h-10" />
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-slate-200">Como posso ajudar hoje?</h3>
                            <p class="text-sm text-slate-500">Pergunte algo sobre o devocional que você está lendo.</p>
                        </div>
                    </div>
                </template>

                <template x-for="(msg, index) in messages" :key="index">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div
                            :class="msg.role === 'user' 
                                    ? 'bg-amber-600 text-white rounded-2xl rounded-tr-none max-w-[85%] p-3 shadow-sm shadow-amber-600/20' 
                                    : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-2xl rounded-tl-none max-w-[85%] p-3 shadow-sm border border-slate-100 dark:border-slate-700'">
                            <p class="text-sm leading-relaxed" x-text="msg.content"></p>
                        </div>
                    </div>
                </template>

                <!-- Loading Indicator -->
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

            <!-- Chat Input Fixo na Base do Modal -->
            <div class="p-1 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 shrink-0 pb-safe">
                <form @submit.prevent="sendToAi" class="p-4 flex items-center gap-2">
                    <input
                        x-model="userInput"
                        type="text"
                        placeholder="Tire suas dúvidas..."
                        class="flex-grow bg-slate-100 dark:bg-slate-800 border-none rounded-2xl px-3 py-3 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none dark:text-white">
                    <button
                        type="submit"
                        :disabled="!userInput.trim() || isAiLoading"
                        class="p-3 bg-amber-600 hover:bg-amber-700 text-white rounded-2xl transition-colors disabled:opacity-50">
                        <x-lucide-send class="w-5 h-5" />
                    </button>
                </form>
            </div>
        </div>

        <!-- Modal do Calendário -->
        <div
            x-show="showCalendar"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display: none;">
            <div
                class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                x-show="showCalendar"
                x-transition.opacity
                @click="showCalendar = false"></div>

            <div
                class="relative w-full max-w-sm bg-white dark:bg-slate-900 rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700"
                x-show="showCalendar"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                <div class="flex items-center justify-between p-4 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-lg font-bold">Selecione o Dia</h2>
                    <button @click="showCalendar = false" type="button" class="p-2 text-slate-500 bg-slate-100 dark:bg-slate-800 rounded-full hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        <x-lucide-x class="w-5 h-5" />
                    </button>
                </div>

                <div class="p-2">
                    <div id="calendar" class="vanilla-calendar light dark:dark !w-full"></div>
                </div>
            </div>
        </div>

        <!-- Footer App Navigation -->
        <nav class="fixed bottom-0 w-full max-w-lg z-40 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 pb-safe">
            <div class="flex items-center justify-between h-16 px-2">
                <a href="/" class="flex flex-col items-center justify-center flex-1 h-full text-amber-600 dark:text-amber-500">
                    <x-lucide-home class="w-6 h-6 mb-1" />
                    <span class="text-[10px] font-medium">Início</span>
                </a>

                <button
                    @click="showAiChat = true"
                    type="button"
                    class="flex flex-col items-center justify-center flex-1 h-full text-slate-400 hover:text-amber-600 transition-colors">
                    <x-lucide-lamp class="w-6 h-6 mb-1" />
                    <span class="text-[10px] font-medium">LampIÃo</span>
                </button>

                <a href="https://iprviamao.com.br/lampada/" target="_blank" class="flex flex-col items-center justify-center flex-1 h-full text-slate-400 hover:text-amber-600 transition-colors">
                    <x-lucide-info class="w-6 h-6 mb-1" />
                    <span class="text-[10px] font-medium">Sobre</span>
                </a>

                <a href="/admin" class="flex flex-col items-center justify-center flex-1 h-full text-slate-400 hover:text-amber-600 transition-colors">
                    <x-lucide-layout-dashboard class="w-6 h-6 mb-1" />
                    <span class="text-[10px] font-medium">Admin</span>
                </a>
            </div>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/build/vanilla-calendar.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach(() => {
                    const calendar = document.querySelector('#calendar');
                    if (calendar && !calendar.dataset.listenerAttached) {
                        calendar.dataset.listenerAttached = true;
                        calendar.addEventListener('click', (e) => {
                            if (e.target.closest('.vc-day')) {
                                setTimeout(() => {
                                    window.dispatchEvent(new CustomEvent('close-calendar'));
                                }, 400);
                            }
                        });
                    }
                });
            });
            const calendarEl = document.getElementById('calendar');
            if (calendarEl) observer.observe(calendarEl, {
                childList: true,
                subtree: true
            });
        });
    </script>

    <!-- Registro do Service Worker (PWA) -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('[SW] Registrado:', reg.scope))
                    .catch(err => console.warn('[SW] Falha no registro:', err));
            });
        }
    </script>
</body>

</html>
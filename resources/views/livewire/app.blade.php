<div class="flex h-screen w-full overflow-hidden">

    <!-- SIDEBAR ESQUERDA (Desktop - LG) -->
    <aside
        class="hidden lg:flex flex-col w-80 bg-slate-50 dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 shrink-0 overflow-y-auto scrollbar-hide">
        <div class="p-5 pb-2">
            <img src="https://storage.googleapis.com/iprviamao-com-br/lampada/logo_lampada_app.webp" alt="Logo Lâmpada"
                class="h-9 w-auto mb-4">
            <nav class="space-y-1">
                <a href="{{ route('app') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 bg-amber-500 text-white rounded-2xl shadow-lg shadow-amber-500/20 font-bold text-sm transition-all">
                    <x-lucide-home class="w-4 h-4 shrink-0" />
                    <div class="flex flex-col text-left leading-tight">
                        <span>Leitura do Dia</span>
                        <span class="text-[10px] font-normal opacity-90 capitalize" x-text="displayShortDate"></span>
                    </div>
                </a>
                <button @click="showAiChat = true"
                    class="w-full flex items-center gap-3 px-3.5 py-2.5 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl transition-all font-medium text-sm xl:hidden">
                    <x-lucide-siren class="w-4 h-4" />
                    <span>Lampião AI</span>
                </button>
                <a href="{{ route('landing', ['sobre' => 1]) }}" target="_self"
                    class="flex items-center gap-3 px-3.5 py-2.5 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl transition-all font-medium text-sm">
                    <x-lucide-info class="w-4 h-4" />
                    <span>Sobre</span>
                </a>
                <a href="/admin"
                    class="flex items-center gap-3 px-3.5 py-2.5 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl transition-all font-medium text-sm">
                    <x-lucide-layout-dashboard class="w-4 h-4" />
                    <span>Painel Admin</span>
                </a>
                <a href="{{ route('logout') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-2xl transition-all font-medium text-sm">
                    <x-lucide-log-out class="w-4 h-4" />
                    <span>Sair</span>
                </a>
            </nav>

            <!-- PAINEL DE GAMIFICAÇÃO (Leitura Anual & Ofensiva Semanal) -->
            <div class="mt-4 space-y-3">
                <!-- Card Ofensiva Semanal -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 border border-slate-200/80 dark:border-slate-700/80 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5">
                            <x-lucide-flame class="w-4 h-4 text-rose-500 animate-pulse" />
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Ofensiva Semanal</span>
                        </div>
                        <span class="text-[11px] font-extrabold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full"
                            x-text="(gamificationData?.current_streak ?? 0) + ' dias 🔥'"></span>
                    </div>

                    <!-- 7 Dias da Semana -->
                    <div class="grid grid-cols-7 gap-1 text-center">
                        <template x-for="dayObj in (gamificationData?.weekly_days ?? [])" :key="dayObj.date">
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-xs font-bold text-slate-500 dark:text-slate-400" x-text="dayObj.day"></span>
                                <div :class="{
                                    'bg-emerald-500 text-white shadow-sm shadow-emerald-500/30': dayObj.completed,
                                    'bg-slate-100 dark:bg-slate-700 text-slate-300 dark:text-slate-600': !dayObj.completed,
                                    'ring-2 ring-amber-500': dayObj.is_today && !dayObj.completed
                                }" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all">
                                    <template x-if="dayObj.completed">
                                        <x-lucide-check class="w-4 h-4 stroke-[3]" />
                                    </template>
                                    <template x-if="!dayObj.completed">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-40"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Card Leitura Anual -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 border border-slate-200/80 dark:border-slate-700/80 shadow-sm">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-1.5">
                            <x-lucide-trophy class="w-4 h-4 text-amber-500" />
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Leitura Anual</span>
                        </div>
                        <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400"
                            x-text="(gamificationData?.annual_percentage ?? 0) + '%'"></span>
                    </div>
                    
                    <!-- Barra de Progresso Anual -->
                    <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden mb-1.5">
                        <div class="bg-gradient-to-r from-amber-500 to-amber-600 h-2 rounded-full transition-all duration-500"
                            :style="'width: ' + (gamificationData?.annual_percentage ?? 0) + '%'"></div>
                    </div>
                    <div class="flex justify-between items-center text-xs font-semibold text-slate-500 dark:text-slate-400">
                        <span>Progresso da Leitura</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200" x-text="(gamificationData?.annual_read_count ?? 0) + ' / ' + (gamificationData?.annual_total_days ?? 365) + ' dias'"></span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Calendário Fixo na Sidebar -->
        <div class="mt-auto p-4">
            <div
                class="bg-white dark:bg-slate-800 rounded-3xl p-2 shadow-sm border border-slate-100 dark:border-slate-700">
                <div id="calendar-sidebar" class="vanilla-calendar light dark:dark !w-full"></div>
                <div x-show="!isToday()" x-cloak x-transition class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                    <button @click="goToToday"
                        class="w-full flex items-center justify-center gap-2 py-2 px-3 bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 font-bold text-xs rounded-2xl transition-all border border-amber-500/20">
                        <x-lucide-calendar-days class="w-4 h-4" />
                        <span>Ir para Hoje</span>
                    </button>
                </div>
            </div>
            <div class="mt-6 flex flex-col items-center gap-2 text-center px-4">
                <img src="https://storage.googleapis.com/iprviamao-com-br/lampada/logo_lampada_app.webp" alt="Logo Lâmpada" class="h-8 w-auto opacity-20 grayscale" />
                <div class="flex items-center gap-3 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                    <a href="{{ route('privacy') }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Privacidade</a>
                    <span>&bull;</span>
                    <a href="{{ route('terms') }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition-colors">Termos</a>
                </div>
                <p class="text-[10px] font-bold text-slate-400 leading-tight">
                    &copy; {{ date('Y') }} Projeto Lâmpada.
                    <span class="block">Todos os direitos reservados.</span>
                </p>
            </div>
        </div>
    </aside>

    <!-- MAIN AREA (Content) -->
    <main
        class="flex-1 flex flex-col min-w-0 bg-white dark:bg-black relative overflow-y-auto scrollbar-hide pb-20 lg:pb-0">

        <!-- Mobile Header -->
        <header
            class="lg:hidden sticky top-0 w-full z-40 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between px-4 h-16">
                <div class="flex items-center gap-3">
                    <img src="https://storage.googleapis.com/iprviamao-com-br/lampada/logo_lampada_app.webp"
                        alt="Logo Lâmpada" class="h-8 w-auto">
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800/80 px-2.5 py-1 rounded-full border border-slate-200/50 dark:border-slate-700/50"
                        x-text="displayShortDate"></span>
                </div>
                <button @click="showCalendar = true"
                    class="p-2 text-amber-600 dark:text-amber-500 rounded-full bg-amber-50 dark:bg-amber-900/30">
                    <x-lucide-calendar class="w-6 h-6" />
                </button>
            </div>
        </header>

        <!-- Container de Conteúdo (Max-width para leitura) -->
        <div class="mx-auto w-full max-w-4xl p-4 lg:p-10">

            <!-- PAINEL DE GAMIFICAÇÃO MOBILE (Visível apenas em telas < LG) -->
            <div class="lg:hidden mb-6 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Card Ofensiva Semanal Mobile -->
                    <div class="bg-slate-50/90 dark:bg-slate-900/60 backdrop-blur-md rounded-3xl p-4 border border-slate-100 dark:border-slate-800/80 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 bg-rose-500/10 text-rose-500 rounded-xl">
                                    <x-lucide-flame class="w-4 h-4 animate-pulse" />
                                </div>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-100">Ofensiva Semanal</span>
                            </div>
                            <span class="text-xs font-extrabold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-full"
                                x-text="(gamificationData?.current_streak ?? 0) + ' dias 🔥'"></span>
                        </div>

                        <!-- 7 Dias da Semana -->
                        <div class="grid grid-cols-7 gap-1 text-center">
                            <template x-for="dayObj in (gamificationData?.weekly_days ?? [])" :key="dayObj.date">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400" x-text="dayObj.day"></span>
                                    <div :class="{
                                        'bg-emerald-500 text-white shadow-sm shadow-emerald-500/30': dayObj.completed,
                                        'bg-slate-200/70 dark:bg-slate-800 text-slate-400 dark:text-slate-500': !dayObj.completed,
                                        'ring-2 ring-amber-500': dayObj.is_today && !dayObj.completed
                                    }" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all">
                                        <template x-if="dayObj.completed">
                                            <x-lucide-check class="w-4 h-4 stroke-[3]" />
                                        </template>
                                        <template x-if="!dayObj.completed">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current opacity-40"></span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Card Leitura Anual Mobile -->
                    <div class="bg-slate-50/90 dark:bg-slate-900/60 backdrop-blur-md rounded-3xl p-4 border border-slate-100 dark:border-slate-800/80 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 bg-amber-500/10 text-amber-500 rounded-xl">
                                    <x-lucide-trophy class="w-4 h-4" />
                                </div>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-100">Leitura Anual</span>
                            </div>
                            <span class="text-xs font-extrabold text-amber-600 dark:text-amber-400"
                                x-text="(gamificationData?.annual_percentage ?? 0) + '%'"></span>
                        </div>

                        <!-- Barra de Progresso Anual -->
                        <div class="w-full bg-slate-200/70 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden my-2">
                            <div class="bg-gradient-to-r from-amber-500 to-amber-600 h-2.5 rounded-full transition-all duration-500"
                                :style="'width: ' + (gamificationData?.annual_percentage ?? 0) + '%'"></div>
                        </div>

                        <div class="flex justify-between items-center text-xs font-semibold text-slate-500 dark:text-slate-400">
                            <span>Progresso da Leitura</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100" x-text="(gamificationData?.annual_read_count ?? 0) + ' / ' + (gamificationData?.annual_total_days ?? 365) + ' dias'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div id="devotional-display" class="relative">
                <div id="devotional-card"
                    class="bg-slate-50 dark:bg-slate-900/50 rounded-[40px] sm:rounded-[60px] border border-slate-100 dark:border-slate-800 p-6 sm:p-12 lg:p-16 relative">

                    <!-- Floating Actions -->
                    <div class="absolute top-6 right-6 lg:top-10 lg:right-10 flex gap-2 z-20">
                        <button @click="openBibleReader" x-show="devotionalData" x-transition
                            class="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-lg shadow-indigo-600/20 transition-all hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            x-cloak>
                            <x-lucide-book-open class="w-5 h-5" />
                            <span class="font-bold text-sm">Ler</span>
                        </button>
                        <button @click="shareOnWhatsApp" x-show="devotionalData" x-transition
                            class="p-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-full shadow-lg shadow-emerald-500/20 transition-all hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                            x-cloak>
                            <x-lucide-share-2 class="w-5 h-5" />
                        </button>
                    </div>

                    <div id="devotional-content" class="prose prose-slate dark:prose-invert max-w-none">

                        <!-- Skeleton Loader -->
                        <template x-if="calDevotionalLoading">
                            <div class="animate-pulse space-y-8">
                                <div class="space-y-3">
                                    <div class="h-6 bg-slate-200 dark:bg-slate-800 rounded-full w-3/4"></div>
                                    <div class="space-y-2">
                                        <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded-full"></div>
                                        <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded-full"></div>
                                        <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded-full w-5/6"></div>
                                    </div>
                                </div>
                                <div class="h-px bg-slate-100 dark:bg-slate-800"></div>
                                <div class="space-y-3">
                                    <div class="h-6 bg-slate-200 dark:bg-slate-800 rounded-full w-2/3"></div>
                                    <div class="space-y-2">
                                        <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded-full"></div>
                                        <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded-full w-4/5"></div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Success State -->
                        <template x-if="!calDevotionalLoading && devotionalData">
                            <div>
                                <div class="mb-8 animate-fade-in-up">
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white leading-tight">
                                            Leitura do Dia
                                        </h2>
                                        <span class="text-xs sm:text-sm font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 px-3.5 py-1 rounded-full border border-amber-200/50 dark:border-amber-800/50 shadow-sm"
                                            x-text="displayShortDate"></span>
                                    </div>
                                </div>

                                <div class="space-y-12 animate-fade-in-up" style="animation-delay: 100ms">
                                    <article>
                                        <div class="flex items-center gap-3 mb-4">
                                            <div
                                                class="h-8 w-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                                <x-lucide-book class="w-5 h-5" />
                                            </div>
                                            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100"
                                                x-text="devotionalData.reference_old_testament"></h3>
                                        </div>
                                        <div class="text-slate-600 dark:text-slate-400 leading-relaxed text-lg"
                                            x-html="devotionalData.content_old_testament"></div>
                                    </article>

                                    <div
                                        class="h-px bg-gradient-to-r from-transparent via-slate-200 dark:via-slate-800 to-transparent">
                                    </div>

                                    <article>
                                        <div class="flex items-center gap-3 mb-4">
                                            <div
                                                class="h-8 w-8 rounded-lg bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                                                <x-lucide-book-open-check class="w-5 h-5" />
                                            </div>
                                            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100"
                                                x-text="devotionalData.reference_new_testament"></h3>
                                        </div>
                                        <div class="text-slate-600 dark:text-slate-400 leading-relaxed text-lg"
                                            x-html="devotionalData.content_new_testament"></div>
                                    </article>
                                </div>
                            </div>
                        </template>

                        <!-- Error State -->
                        <template x-if="!calDevotionalLoading && error">
                            <div class="flex flex-col items-center justify-center py-20 text-center space-y-4">
                                <div class="p-4 bg-amber-50 dark:bg-amber-900/30 rounded-full text-amber-500">
                                    <x-lucide-alert-triangle class="w-12 h-12" />
                                </div>
                                <h3 class="text-xl font-bold">Conteúdo não encontrado</h3>
                                <p class="text-slate-500 max-w-xs" x-text="error"></p>
                            </div>
                        </template>

                        <!-- Initial State -->
                        <template x-if="!calDevotionalLoading && !devotionalData && !error">
                            <div class="flex flex-col items-center justify-center py-20 text-center space-y-4">
                                <div class="p-4 bg-slate-100 dark:bg-slate-800 rounded-full">
                                    <x-lucide-calendar-days class="w-12 h-12 text-slate-300" />
                                </div>
                                <p class="text-slate-500">Selecione uma data para começar sau leitura.</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Leitura Bíblica (dentro do Main) -->
        <div x-show="showBibleModal"
            class="fixed inset-x-0 top-0 bottom-16 lg:absolute lg:inset-0 z-[110] flex flex-col bg-white dark:bg-slate-950"
            x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-300 transform"
            x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full" x-cloak>

            <header
                class="h-16 lg:h-20 flex items-center justify-between px-4 lg:px-10 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 rounded-xl">
                        <x-lucide-book-open class="w-5 h-5 lg:w-6 lg:h-6" />
                    </div>
                    <h2 class="font-bold text-lg lg:text-xl">Leitura Bíblica</h2>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Botão Auto-Scroll -->
                    <button @click="toggleAutoScroll"
                        :class="isAutoScrolling ? 'bg-amber-100 text-amber-600 border-amber-200 dark:bg-amber-900/30 dark:border-amber-700' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700'"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl border transition-all hover:scale-105 active:scale-95">
                        <template x-if="isAutoScrolling">
                            <x-lucide-pause-circle class="w-5 h-5 animate-pulse" />
                        </template>
                        <template x-if="!isAutoScrolling">
                            <x-lucide-scroll-text class="w-5 h-5" />
                        </template>
                        <span class="text-xs font-bold hidden sm:inline"
                            x-text="isAutoScrolling ? 'Pausar Rolagem' : 'Auto Rolagem'"></span>
                    </button>

                    <!-- Botão Leitura em Voz Alta (TTS) -->
                    <div class="relative">
                        <div class="flex items-center gap-1">
                            <button @click="toggleTts"
                                :class="isTtsLoading ? 'bg-amber-100 text-amber-600 border-amber-200 dark:bg-amber-900/30 dark:border-amber-700' : (isSpeaking && !isPaused ? 'bg-violet-100 text-violet-600 border-violet-200 dark:bg-violet-900/30 dark:border-violet-700' : (isSpeaking && isPaused ? 'bg-sky-100 text-sky-600 border-sky-200 dark:bg-sky-900/30 dark:border-sky-700' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700'))"
                                class="flex items-center gap-2 px-4 py-2 rounded-xl border transition-all hover:scale-105 active:scale-95 disabled:opacity-50"
                                :disabled="isTtsLoading"
                                :title="isSpeaking && !isPaused ? 'Pausar leitura' : (isSpeaking && isPaused ? 'Retomar leitura' : 'Ler em voz alta')">
                                <template x-if="isTtsLoading">
                                    <x-lucide-loader class="w-5 h-5 animate-spin" />
                                </template>
                                <template x-if="!isTtsLoading && (isSpeaking && !isPaused)">
                                    <x-lucide-mic class="w-5 h-5 animate-pulse" />
                                </template>
                                <template x-if="!isTtsLoading && (isSpeaking && isPaused)">
                                    <x-lucide-play class="w-5 h-5" />
                                </template>
                                <template x-if="!isTtsLoading && !isSpeaking">
                                    <x-lucide-volume-2 class="w-5 h-5" />
                                </template>
                                <span class="text-xs font-bold hidden sm:inline"
                                    x-text="isTtsLoading ? 'Gerando...' : (isSpeaking && !isPaused ? 'Pausar' : (isSpeaking && isPaused ? 'Retomar' : 'Ouvir'))"></span>
                            </button>

                            <!-- Engrenagem de configurações TTS -->
                            <button @click="showTtsSettings = !showTtsSettings"
                                :class="showTtsSettings ? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'"
                                class="p-1.5 rounded-lg transition-all hover:scale-105 active:scale-95"
                                title="Configurações de voz">
                                <x-lucide-settings-2 class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Painel de configurações TTS -->
                        <div x-show="showTtsSettings" @click.outside="showTtsSettings = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                            class="absolute right-0 top-full mt-2 w-72 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 p-4 z-50"
                            x-cloak>

                            <!-- Toggle: Anunciar versículos -->
                            <!-- <div
                                class="flex items-center justify-between mb-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Anunciar
                                        versículos</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Ler "Versículo 1, 2..." ou texto corrido
                                    </p>
                                </div>
                                <button @click="ttsAnnounceVerses = !ttsAnnounceVerses"
                                    :class="ttsAnnounceVerses ? 'bg-violet-600' : 'bg-slate-200 dark:bg-slate-700'"
                                    class="relative w-11 h-6 rounded-full transition-colors shrink-0 ml-3">
                                    <span :class="ttsAnnounceVerses ? 'translate-x-5' : 'translate-x-1'"
                                        class="absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200 ease-in-out block">
                                    </span>
                                </button>
                            </div> -->

                            <!-- Toggle: Música de Foco -->
                            <div
                                class="flex items-center justify-between mb-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Música de foco
                                    </p>
                                    <!-- <p class="text-xs text-slate-400 mt-0.5">Inicia automaticamente com a leitura</p> -->
                                </div>
                                <button @click="ttsAutoMusic = !ttsAutoMusic; updateMusicRealtime()"
                                    :class="ttsAutoMusic ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700'"
                                    class="relative w-11 h-6 rounded-full transition-colors shrink-0 ml-3">
                                    <span :class="ttsAutoMusic ? 'translate-x-5' : 'translate-x-1'"
                                        class="absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200 ease-in-out block">
                                    </span>
                                </button>
                            </div>

                            <!-- Velocidade -->
                            <!-- <div class="mb-4">
                                <p
                                    class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                                    Velocidade</p>
                                <div class="flex gap-1.5 flex-wrap">
                                    <template x-for="rate in [0.5, 0.75, 1.0, 1.25, 1.5]" :key="rate">
                                        <button @click="ttsRate = rate; updateTtsRealtime()"
                                            :class="ttsRate === rate ? 'bg-violet-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                                            x-text="rate + '×'">
                                        </button>
                                    </template>
                                </div>
                            </div> -->

                        </div>
                    </div>

                    <button @click="closeBibleModal"
                        class="p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full">
                        <x-lucide-x class="w-6 h-6 lg:w-8 lg:h-8" />
                    </button>
                </div>
            </header>


            <div x-ref="bibleContainer" @scroll="checkBibleScrollEnd" class="flex-grow overflow-y-auto p-6 lg:p-20 scrollbar-hide pb-20">
                <div class="max-w-4xl mx-auto">
                    <template x-if="bibleLoading">
                        <div class="flex flex-col items-center justify-center h-64 space-y-4">
                            <div
                                class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin">
                            </div>
                            <p class="text-slate-500 animate-pulse text-lg font-medium">Conectando à Bíblia Digital...
                            </p>
                        </div>
                    </template>

                    <template x-if="!bibleLoading && bibleData">
                        <div class="space-y-16">
                            <!-- Antigo Testamento -->
                            <template x-if="bibleData.old_testament && bibleData.old_testament.success">
                                <div class="space-y-12">
                                    <template x-for="chapter in bibleData.old_testament.chapters" :key="chapter.number">
                                        <section>
                                            <h3 class="text-3xl font-bold text-slate-900 dark:text-white mb-8 border-l-8 border-indigo-500 pl-6"
                                                x-text="bibleData.old_testament.book_name + ' ' + chapter.number"></h3>
                                            <div class="space-y-6">
                                                <template x-for="verse in chapter.verses" :key="verse.number">
                                                    <p
                                                        class="text-xl lg:text-2xl leading-relaxed text-slate-700 dark:text-slate-300 font-serif">
                                                        <sup class="text-sm font-bold text-indigo-500 mr-2"
                                                            x-text="verse.number"></sup>
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
                                            <h3 class="text-3xl font-bold text-slate-900 dark:text-white mb-8 border-l-8 border-rose-500 pl-6"
                                                x-text="bibleData.new_testament.book_name + ' ' + chapter.number"></h3>
                                            <div class="space-y-6">
                                                <template x-for="verse in chapter.verses" :key="verse.number">
                                                    <p
                                                        class="text-xl lg:text-2xl leading-relaxed text-slate-700 dark:text-slate-300 font-serif">
                                                        <sup class="text-sm font-bold text-rose-500 mr-2"
                                                            x-text="verse.number"></sup>
                                                        <span x-text="verse.text"></span>
                                                    </p>
                                                </template>
                                            </div>
                                        </section>
                                    </template>
                                </div>
                            </template>

                            <template
                                x-if="(!bibleData.old_testament || !bibleData.old_testament.success) && (!bibleData.new_testament || !bibleData.new_testament.success)">
                                <div class="text-center py-20 bg-slate-50 dark:bg-slate-900 rounded-[40px]">
                                    <x-lucide-alert-circle class="w-16 h-16 text-slate-300 mx-auto mb-6" />
                                    <p class="text-slate-500 text-xl">Não foi possível carregar o texto bíblico para
                                        estas referências.</p>
                                </div>
                            </template>

                            <!-- Botão de OK de Leitura -->
                            <div class="pt-20 pb-10 flex flex-col items-center">
                                <div class="w-20 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full mb-12"></div>
                                <button
                                    @click="const whatsappUrl = `https://wa.me/?text=${encodeURIComponent('Leitura do dia 🆗')}`; window.open(whatsappUrl, '_blank');"
                                    class="flex items-center gap-3 px-12 py-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-3xl shadow-2xl shadow-emerald-600/30 transition-all hover:scale-105 active:scale-95 text-xl font-extrabold group">
                                    <x-lucide-share-2 class="w-7 h-7 group-hover:rotate-12 transition-transform" />
                                    Compartilhar OK de Leitura
                                </button>
                                <p class="mt-6 text-slate-400 dark:text-slate-500 text-base">Notifique seu grupo que
                                    você completou a leitura!</p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </main>

    <!-- SIDEBAR DIREITA (Desktop - XL - IA Chat Persistente) -->
    <aside
        class="hidden xl:flex flex-col w-[400px] bg-slate-50 dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shrink-0">
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
                        <p class="text-sm">Tire suas dúvidas sobre a leitura de hoje comigo.</p>
                    </div>
                </div>
            </template>
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div
                        :class="msg.role === 'user' ? 'bg-amber-600 text-white rounded-2xl rounded-tr-none max-w-[90%] p-4 shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-2xl rounded-tl-none max-w-[90%] p-4 shadow-sm border border-slate-100 dark:border-slate-700'">
                        <div class="text-sm leading-relaxed whitespace-pre-line"
                            x-html="msg.role === 'ai' ? formatMarkdown(msg.content) : msg.content"></div>
                    </div>
                </div>
            </template>
            <div x-show="isAiLoading" class="flex justify-start">
                <div
                    class="bg-white dark:bg-slate-800 p-3 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 dark:border-slate-700">
                    <div class="flex gap-1">
                        <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce"></div>
                        <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                        <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.4s]"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
            <form @submit.prevent="sendToAi"
                class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800 rounded-2xl p-1">
                <input x-model="userInput" type="text" placeholder="Pergunte algo..."
                    class="flex-1 bg-transparent border-none px-4 py-3 text-sm focus:ring-0 focus:outline-none dark:text-white">
                <button type="submit" :disabled="!userInput.trim() || isAiLoading"
                    class="p-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl transition-colors disabled:opacity-50">
                    <x-lucide-send class="w-5 h-5" />
                </button>
            </form>
        </div>
    </aside>

    <!-- MOBILE NAVIGATION (Fixed Bottom) -->
    <nav
        class="lg:hidden fixed bottom-0 left-0 w-full z-40 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 pb-safe">
        <div class="flex items-center justify-between h-16 px-4">
            <a href="{{ route('app') }}" class="flex flex-col items-center justify-center flex-1 text-amber-600 dark:text-amber-500">
                <x-lucide-home class="w-6 h-6 mb-1" />
                <span class="text-[10px] font-medium">Início</span>
            </a>
            <button @click="showAiChat = true" class="flex flex-col items-center justify-center flex-1 text-slate-400">
                <x-lucide-siren class="w-6 h-6 mb-1" />
                <span class="text-[10px] font-medium">Lampião</span>
            </button>
            <a href="{{ route('landing', ['sobre' => 1]) }}" target="_self"
                class="flex flex-col items-center justify-center flex-1 text-slate-400">
                <x-lucide-info class="w-6 h-6 mb-1" />
                <span class="text-[10px] font-medium">Sobre</span>
            </a>
            <a href="/admin" class="flex flex-col items-center justify-center flex-1 text-slate-400">
                <x-lucide-layout-dashboard class="w-6 h-6 mb-1" />
                <span class="text-[10px] font-medium">Admin</span>
            </a>
            <a href="{{ route('logout') }}" class="flex flex-col items-center justify-center flex-1 text-rose-500">
                <x-lucide-log-out class="w-6 h-6 mb-1" />
                <span class="text-[10px] font-medium">Sair</span>
            </a>
        </div>
    </nav>

    <!-- </div> -->

    <!-- MODALS (Mobile Context) -->

    <!-- Modal do Chat IA (Mobile/Tablet) -->
    <div x-show="showAiChat" class="xl:hidden fixed inset-0 z-[120] bg-slate-50 dark:bg-slate-950 flex flex-col"
        x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-300 transform"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" x-cloak>
        <header
            class="h-16 flex items-center justify-between px-4 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shrink-0">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-100 dark:bg-amber-900/30 text-amber-600 rounded-xl">
                    <x-lucide-siren class="w-5 h-5" />
                </div>
                <h2 class="font-bold text-lg">Assistente Lampião</h2>
            </div>
            <button @click="showAiChat = false"
                class="p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full">
                <x-lucide-x class="w-6 h-6" />
            </button>
        </header>

        <div id="chat-messages-container-modal"
            class="flex-grow overflow-y-auto p-4 space-y-4 scrollbar-hide bg-slate-50 dark:bg-slate-950 pb-10">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div
                        :class="msg.role === 'user' ? 'bg-amber-600 text-white rounded-2xl rounded-tr-none max-w-[85%] p-3 shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-2xl rounded-tl-none max-w-[85%] p-3 shadow-sm border border-slate-100 dark:border-slate-700'">
                        <div class="text-sm leading-relaxed whitespace-pre-line"
                            x-html="msg.role === 'ai' ? formatMarkdown(msg.content) : msg.content"></div>
                    </div>
                </div>
            </template>
            <div x-show="isAiLoading" class="flex justify-start">
                <div
                    class="bg-white dark:bg-slate-800 p-3 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 dark:border-slate-700">
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
                <input x-model="userInput" type="text" placeholder="Tire suas dúvidas..."
                    class="flex-grow bg-slate-100 dark:bg-slate-800 border-none rounded-2xl px-3 py-3 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none dark:text-white">
                <button type="submit" :disabled="!userInput.trim() || isAiLoading"
                    class="p-3 bg-amber-600 hover:bg-amber-700 text-white rounded-2xl transition-colors disabled:opacity-50">
                    <x-lucide-send class="w-5 h-5" />
                </button>
            </form>
        </div>
    </div>

    <!-- Modal do Calendário (Mobile) -->
    <div x-show="showCalendar" class="fixed inset-0 z-50 flex items-center justify-center p-4 lg:hidden" x-cloak>
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" x-show="showCalendar" x-transition.opacity
            @click="showCalendar = false"></div>
        <div class="relative w-full max-w-sm bg-white dark:bg-slate-900 rounded-[40px] shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700"
            x-show="showCalendar" x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-lg font-bold">Selecione o Dia</h2>
                <button @click="showCalendar = false"
                    class="p-2 text-slate-500 bg-slate-100 dark:bg-slate-800 rounded-full">
                    <x-lucide-x class="w-5 h-5" />
                </button>
            </div>
            <div class="p-4">
                <div id="calendar-modal" class="vanilla-calendar light dark:dark !w-full"></div>
                <div x-show="!isToday()" x-cloak x-transition class="mt-3">
                    <button @click="goToToday"
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm rounded-2xl shadow-lg shadow-amber-500/20 transition-all">
                        <x-lucide-calendar-days class="w-5 h-5" />
                        <span>Ir para Hoje</span>
                    </button>
                </div>
            </div>
        </div>
    </div>


</div>
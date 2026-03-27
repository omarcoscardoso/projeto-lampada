import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const calendarElement = document.getElementById('calendar');
    const devotionalContent = document.getElementById('devotional-content');
    const shareButton = document.getElementById('whatsapp-share-btn');
    let currentDevotional = null;

    // Helper to format HTML for WhatsApp (Optimized fallback version)
    function formatForWhatsApp(html) {
        if (!html) return '';

        let formatted = html
            // 1. Explicit line breaks
            .replace(/<br\s*\/?>/gi, '\n')
            // 2. Headings (h1-h6) become Bold
            .replace(/<h[1-6][^>]*>/gi, '\n\n*')
            .replace(/<\/h[1-6]>/gi, '*\n')
            // 3. Block tags correctly handled
            .replace(/<(p|div|section)[^>]*>/gi, '\n')
            .replace(/<\/(p|div|section)>/gi, '\n')
            // 4. Lists: use a simple hyphen bullet
            .replace(/<li[^>]*>/gi, '\n- ')
            .replace(/<\/li>/gi, '\n')
            .replace(/<\/?(ul|ol)[^>]*>/gi, '\n')
            // 5. Text style (Bold, Italic, Strikethrough, Monospace)
            .replace(/<(b|strong)[^>]*>/gi, '*')
            .replace(/<\/(b|strong)>/gi, '*')
            .replace(/<(i|em)[^>]*>/gi, '_')
            .replace(/<\/(i|em)>/gi, '_')
            .replace(/<(s|strike|del)[^>]*>/gi, '~')
            .replace(/<\/(s|strike|del)>/gi, '~')
            .replace(/<(code|pre)[^>]*>/gi, '```')
            .replace(/<\/(code|pre)>/gi, '```');

        // 6. Remove remaining tags and decode entities
        let tmp = document.createElement("DIV");
        tmp.innerHTML = formatted;
        let text = tmp.textContent || tmp.innerText || "";

        // 7. Final cleanup
        // Step A: Replace bullet followed immediately by newline (common with nested tags like <li><p>)
        text = text.replace(/(-)\s*[\r\n]+/g, '$1 ');

        // Step B: Clean up whitespace on each line but preserve bullets
        text = text
            .split('\n')
            .map(line => {
                const trimmed = line.trim();
                if (trimmed.startsWith('-')) {
                    // Ensure there is only one space after hyphen
                    return '- ' + trimmed.substring(1).trim();
                }
                return trimmed;
            })
            .join('\n');

        // Step C: Collapse excessive newlines
        // First collapse any sequence of newlines before a bullet into a SINGLE newline
        text = text.replace(/\n+(- )/g, '\n$1');

        // Then collapse other sequences of 3+ newlines to 2 (maximum 1 blank line between paragraphs)
        return text.replace(/\n{3,}/g, '\n\n').trim();
    }

    // Skeleton loader HTML
    const skeletonLoader = `
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
    `;

    if (calendarElement && devotionalContent && shareButton) {
        shareButton.addEventListener('click', () => {
            if (!currentDevotional) return;

            // Prefer pre-formatted message from backend if available
            const message = currentDevotional.whatsapp_message ||
                `*${currentDevotional.reference_old_testament}*\n${formatForWhatsApp(currentDevotional.content_old_testament)}\n\n*${currentDevotional.reference_new_testament}*\n${formatForWhatsApp(currentDevotional.content_new_testament)}`;

            // Using wa.me endpoint which is the modern standard for sharing
            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message.trim())}`;
            window.open(whatsappUrl, '_blank');
        });

        const calendar = new VanillaCalendar(calendarElement, {
            settings: {
                lang: 'pt',
                visibility: {
                    theme: 'system',
                },
            },
            actions: {
                clickDay(event, self) {
                    if (self.selectedDates && self.selectedDates[0]) {
                        const dateString = self.selectedDates[0];
                        const selectedDate = new Date(dateString + 'T00:00:00');
                        const yyyy = selectedDate.getFullYear();
                        const mm = String(selectedDate.getMonth() + 1).padStart(2, '0');
                        const dd = String(selectedDate.getDate()).padStart(2, '0');
                        const formattedDate = `${yyyy}-${mm}-${dd}`;
                        fetchDevotional(formattedDate);

                        // Dispara o evento global para o Alpine fechar o modal
                        window.dispatchEvent(new CustomEvent('close-calendar'));
                    }
                },
            },
        });

        calendar.init();

        async function fetchDevotional(date) {
            devotionalContent.innerHTML = skeletonLoader;
            shareButton.classList.add('hidden', 'scale-90', 'opacity-0');
            shareButton.classList.remove('flex'); // Just in case
            currentDevotional = null;

            try {
                const response = await fetch(`/api/devotionals/${date}`);
                const data = await response.json();

                // Add a small delay for a smoother feel if it's too fast
                await new Promise(resolve => setTimeout(resolve, 300));

                if (response.ok) {
                    currentDevotional = data;

                    // Format date for display
                    const dateObj = new Date(date + 'T00:00:00');
                    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    const displayDate = dateObj.toLocaleDateString('pt-BR', options);

                    devotionalContent.innerHTML = `
                        <div class="mb-10 animate-fade-in-up">
                            <span class="inline-block px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-xs font-semibold tracking-wider uppercase mb-3">
                                Devocional do Dia
                            </span>
                            <h2 class="text-3xl font-bold text-slate-900 dark:text-white leading-tight capitalize">
                                ${displayDate}
                            </h2>
                        </div>
                        
                        <div class="space-y-12 animate-fade-in-up" style="animation-delay: 100ms">
                            <article>
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="h-8 w-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center" title="Antigo Testamento">
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h12a2 2 0 0 0 2-2v-2H10v2a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v3h4"/><path d="M19 17V5a2 2 0 0 0-2-2H4"/></svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100">${data.reference_old_testament}</h3>
                                </div>
                                <div class="text-slate-600 dark:text-slate-400 leading-relaxed text-lg">
                                    ${data.content_old_testament}
                                </div>
                            </article>

                            <div class="h-px bg-gradient-to-r from-transparent via-slate-200 dark:via-slate-800 to-transparent"></div>

                            <article>
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="h-8 w-8 rounded-lg bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center" title="Novo Testamento">
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100">${data.reference_new_testament}</h3>
                                </div>
                                <div class="text-slate-600 dark:text-slate-400 leading-relaxed text-lg">
                                    ${data.content_new_testament}
                                </div>
                            </article>
                        </div>
                    `;

                    shareButton.classList.remove('hidden');
                    setTimeout(() => {
                        shareButton.classList.remove('scale-90', 'opacity-0');
                        shareButton.classList.add('scale-100', 'opacity-100');
                    }, 50);
                } else {
                    devotionalContent.innerHTML = `
                        <div class="flex flex-col items-center justify-center py-20 text-center space-y-4">
                            <div class="p-4 bg-amber-50 dark:bg-amber-900/30 rounded-full">
                                <svg class="w-12 h-12 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold">Conteúdo não encontrado</h3>
                            <p class="text-slate-500 max-w-xs">${data.message || 'Não há devocional registrado para esta data.'}</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error fetching devotional:', error);
                devotionalContent.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-20 text-center space-y-4">
                        <div class="p-4 bg-rose-50 dark:bg-rose-900/30 rounded-full">
                            <svg class="w-12 h-12 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold">Erro na conexão</h3>
                        <p class="text-slate-500">Não foi possível carregar os dados. Verifique sua internet.</p>
                        <button onclick="location.reload()" class="mt-4 px-6 py-2 bg-slate-900 dark:bg-white dark:text-black text-white rounded-full font-semibold">Tentar novamente</button>
                    </div>
                `;
            }
        }

        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const formattedToday = `${yyyy}-${mm}-${dd}`;
        fetchDevotional(formattedToday);
    }
});

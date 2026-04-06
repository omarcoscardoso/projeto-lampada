import { Calendar } from 'vanilla-calendar-pro';

export default () => ({
    showCalendar: false,
    showAiChat: false,
    showBibleModal: false,
    messages: [],
    userInput: '',
    isAiLoading: false,
    bibleLoading: false,
    bibleData: null,
    isAutoScrolling: false,
    scrollPos: 0,

    // Devotional specific data
    currentDate: null,
    devotionalData: null,
    isDevotionalLoading: false,
    error: null,

    init() {
        this.currentDate = this.getFormattedDate(new Date());
        this.initCalendars();
        this.fetchDevotional(this.currentDate);
        this.initMobileCalendarObserver();
    },

    getFormattedDate(date) {
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    },

    initCalendars() {
        const component = this;
        const calendarSelectors = ['#calendar-sidebar', '#calendar-modal'];
        calendarSelectors.forEach(selector => {
            const el = document.querySelector(selector);
            if (el) {
                const calendar = new Calendar(el, {
                    settings: {
                        lang: 'pt',
                        selection: {
                            day: 'single',
                        },
                        visibility: {
                            theme: 'system',
                        },
                    },
                    onClickDate(self) {
                        console.log('Calendar date clicked. Selected dates:', self.context.selectedDates);
                        const selectedDate = self.context.selectedDates[0];
                        if (selectedDate) {
                            component.currentDate = selectedDate;
                            component.fetchDevotional(selectedDate);
                            component.showCalendar = false;
                        }
                    },
                });
                console.log('Initializing calendar for selector:', selector);
                calendar.init();
            }
        });
    },

    initMobileCalendarObserver() {
        // Dispatch global event for Alpine (useful for mobile modal)
        window.addEventListener('close-calendar', () => {
            this.showCalendar = false;
        });
    },

    async fetchDevotional(date) {
        this.isDevotionalLoading = true;
        this.error = null;
        this.devotionalData = null;

        try {
            const response = await fetch(`/api/devotionals/${date}`);
            const data = await response.json();

            // Add a small delay for a smoother feel
            await new Promise(resolve => setTimeout(resolve, 300));

            if (response.ok) {
                this.devotionalData = data;
            } else {
                this.error = data.message || 'Não há devocional registrado para esta data.';
            }
        } catch (e) {
            console.error('Error fetching devotional:', e);
            this.error = 'Não foi possível carregar os dados. Verifique sua internet.';
        } finally {
            this.isDevotionalLoading = false;
        }
    },

    get displayDate() {
        if (!this.currentDate) return '';
        const dateObj = new Date(this.currentDate + 'T00:00:00');
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        return dateObj.toLocaleDateString('pt-BR', options);
    },

    shareOnWhatsApp() {
        if (!this.devotionalData) return;

        const message = this.devotionalData.whatsapp_message ||
            `*${this.devotionalData.reference_old_testament}*\n${this.formatForWhatsApp(this.devotionalData.content_old_testament)}\n\n*${this.devotionalData.reference_new_testament}*\n${this.formatForWhatsApp(this.devotionalData.content_new_testament)}`;

        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message.trim())}`;
        window.open(whatsappUrl, '_blank');
    },

    // Helper to format HTML for WhatsApp
    formatForWhatsApp(html) {
        if (!html) return '';
        let formatted = html
            .replace(/<br\s*\/?>/gi, '\n')
            .replace(/<h[1-6][^>]*>/gi, '\n\n*')
            .replace(/<\/h[1-6]>/gi, '*\n')
            .replace(/<(p|div|section)[^>]*>/gi, '\n')
            .replace(/<\/(p|div|section)>/gi, '\n')
            .replace(/<li[^>]*>/gi, '\n- ')
            .replace(/<\/li>/gi, '\n')
            .replace(/<\/?(ul|ol)[^>]*>/gi, '\n')
            .replace(/<(b|strong)[^>]*>/gi, '*')
            .replace(/<\/(b|strong)>/gi, '*')
            .replace(/<(i|em)[^>]*>/gi, '_')
            .replace(/<\/(i|em)>/gi, '_')
            .replace(/<(s|strike|del)[^>]*>/gi, '~')
            .replace(/<\/(s|strike|del)>/gi, '~')
            .replace(/<(code|pre)[^>]*>/gi, '```')
            .replace(/<\/(code|pre)>/gi, '```');

        let tmp = document.createElement("DIV");
        tmp.innerHTML = formatted;
        let text = tmp.textContent || tmp.innerText || "";

        text = text.replace(/(-)\s*[\r\n]+/g, '$1 ');
        text = text.split('\n').map(line => {
            const trimmed = line.trim();
            if (trimmed.startsWith('-')) return '- ' + trimmed.substring(1).trim();
            return trimmed;
        }).join('\n');

        text = text.replace(/\n+(- )/g, '\n$1');
        return text.replace(/\n{3,}/g, '\n\n').trim();
    },

    async sendToAi() {
        if (!this.userInput.trim() || this.isAiLoading) return;

        const userMsg = this.userInput;
        this.messages.push({ role: 'user', content: userMsg });
        this.userInput = '';
        this.isAiLoading = true;

        const context = this.devotionalData ?
            `${this.devotionalData.content_old_testament} ${this.devotionalData.content_new_testament}`.replace(/<[^>]*>/g, '') : '';

        this.$nextTick(() => { this.scrollToBottom(); });

        try {
            const response = await fetch('/api/ai/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
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

    toggleAutoScroll() {
        this.isAutoScrolling = !this.isAutoScrolling;
        if (this.isAutoScrolling) {
            const container = this.$refs.bibleContainer;
            if (!container) return;

            this.scrollPos = container.scrollTop;

            const scrollStep = () => {
                if (!this.isAutoScrolling) return;

                this.scrollPos += 0.15;
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
        if (!this.devotionalData) return;

        this.showBibleModal = true;
        this.isAutoScrolling = false;
        this.bibleLoading = true;
        this.bibleData = null;

        const refOld = this.devotionalData.reference_old_testament;
        const refNew = this.devotionalData.reference_new_testament;

        try {
            const response = await fetch(`/api/bible/read?ref_old=${encodeURIComponent(refOld || '')}&ref_new=${encodeURIComponent(refNew || '')}`);
            this.bibleData = await response.json();
        } catch (e) {
            console.error('Erro ao buscar texto bíblico', e);
        } finally {
            this.bibleLoading = false;
        }
    }
});

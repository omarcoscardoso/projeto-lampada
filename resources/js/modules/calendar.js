import { Calendar } from 'vanilla-calendar-pro';

export const calendarApp = () => ({
    showCalendar: false,
    currentDate: null,
    devotionalData: null,
    calDevotionalLoading: false,
    calError: null,
    calendarInstances: [],
    displayShortDate: '',

    init() {
        // Define a data inicial como hoje
        this.currentDate = this.getFormattedDate(new Date());
        this.updateDisplayDates();

        // Inicializa as instâncias do calendário
        this.initCalendars();

        // Busca os dados da data atual
        this.fetchDevotional(this.currentDate);

        // Listener para fechar via evento global (útil para mobile)
        this.initMobileCalendarObserver();

        // Inicializa a busca dos dados de gamificação
        this.initGamification();
    },

    /**
     * Atualiza a propriedade reativa de exibição de data simples (DD/MM/YYYY)
     */
    updateDisplayDates() {
        if (!this.currentDate) {
            this.displayShortDate = '';
            return;
        }
        const dateObj = new Date(this.currentDate + 'T00:00:00');
        this.displayShortDate = dateObj.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    },

    /**
     * Formata data para o padrão YYYY-MM-DD (usado na API e Calendar)
     */
    getFormattedDate(date) {
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    },

    /**
     * Retorna true se a data exibida for a data de hoje
     */
    isToday() {
        if (!this.currentDate) return true;
        return this.currentDate === this.getFormattedDate(new Date());
    },

    /**
     * Leva o usuário para o dia de hoje
     */
    goToToday() {
        const todayStr = this.getFormattedDate(new Date());
        if (this.currentDate !== todayStr) {
            this.currentDate = todayStr;
            this.updateDisplayDates();
            this.fetchDevotional(todayStr);

            const now = new Date();
            const year = now.getFullYear();
            const month = now.getMonth();

            this.calendarInstances.forEach(calendar => {
                if (calendar) {
                    calendar.selectedDates = [todayStr];
                    calendar.selectedYear = year;
                    calendar.selectedMonth = month;
                    calendar.update({
                        dates: true,
                        year: true,
                        month: true,
                    });
                }
            });
        }
        this.showCalendar = false;
        this.applyCalendarHighlights();
    },

    /**
     * Configura e renderiza o Vanilla Calendar Pro
     */
    initCalendars() {
        const component = this;
        // Seleciona o calendário do sidebar (desktop) e do modal (mobile)
        const calendarSelectors = ['#calendar-sidebar', '#calendar-modal'];
        this.calendarInstances = [];

        calendarSelectors.forEach(selector => {
            const el = document.querySelector(selector);
            if (el) {
                const calendar = new Calendar(el, {
                    locale: 'pt-BR',
                    selectionDayMode: 'single',
                    visibilityThemeDetect: true,
                    onClickDate(self) {
                        const selectedDate = self.context.selectedDates[0];
                        if (selectedDate) {
                            component.currentDate = selectedDate;
                            component.updateDisplayDates();
                            component.fetchDevotional(selectedDate);
                            component.showCalendar = false;
                        }
                    },
                    onClickMonth() {
                        setTimeout(() => component.applyCalendarHighlights(), 100);
                    },
                    onClickYear() {
                        setTimeout(() => component.applyCalendarHighlights(), 100);
                    },
                });
                calendar.init();
                component.calendarInstances.push(calendar);
            }
        });

        setTimeout(() => component.applyCalendarHighlights(), 100);
    },

    initMobileCalendarObserver() {
        window.addEventListener('close-calendar', () => {
            this.showCalendar = false;
        });
    },

    /**
     * Busca os dados no servidor baseados na data selecionada
     */
    async fetchDevotional(date) {
        this.currentDate = date;
        this.updateDisplayDates();
        this.calDevotionalLoading = true;
        this.calError = null;
        this.devotionalData = null;

        try {
            const response = await fetch(`/api/devotionals/${date}`);
            const data = await response.json();

            // Pequeno delay para suavizar a transição de interface
            await new Promise(resolve => setTimeout(resolve, 300));

            if (response.ok) {
                this.devotionalData = data;

                // Se o leitor bíblico estiver aberto, recarrega o texto para a nova data
                if (this.showBibleModal) {
                    this.openBibleReader();
                }
            } else {
                this.calError = data.message || 'Não há conteúdo para esta data.';
            }
        } catch (e) {
            console.error('Error fetching devotional:', e);
            this.calError = 'Erro de conexão ao carregar dados.';
        } finally {
            this.calDevotionalLoading = false;
        }
    },

    getDisplayShortDate() {
        return this.displayShortDate;
    },
});
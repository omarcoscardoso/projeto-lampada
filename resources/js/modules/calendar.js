import { Calendar } from 'vanilla-calendar-pro';

export const calendarApp = () => ({
    showCalendar: false,
    currentDate: null,
    devotionalData: null,
    calDevotionalLoading: false,
    calError: null,
    calendarInstances: [],

    init() {
        // Define a data inicial como hoje
        this.currentDate = this.getFormattedDate(new Date());

        // Inicializa as instâncias do calendário
        this.initCalendars();

        // Busca os dados da data atual
        this.fetchDevotional(this.currentDate);

        // Listener para fechar via evento global (útil para mobile)
        this.initMobileCalendarObserver();
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
                            component.fetchDevotional(selectedDate);
                            component.showCalendar = false;
                        }
                    },
                });
                calendar.init();
                component.calendarInstances.push(calendar);
            }
        });
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
        this.calD = true;
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
            this.calD = false;
        }
    },

    /**
     * Getter para exibir a data por extenso na interface (ex: "segunda-feira, 8 de abril...")
     */
    get displayDate() {
        if (!this.currentDate) return '';
        const dateObj = new Date(this.currentDate + 'T00:00:00');
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        return dateObj.toLocaleDateString('pt-BR', options);
    },
});
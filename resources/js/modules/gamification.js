export const gamificationApp = () => ({
    gamificationData: null,
    gamificationLoading: false,
    isMarkingComplete: false,

    initGamification() {
        this.fetchGamificationData();
    },

    async fetchGamificationData() {
        this.gamificationLoading = true;
        try {
            const res = await fetch('/api/user/gamification', {
                headers: {
                    'Accept': 'application/json',
                }
            });
            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    this.gamificationData = data.data;
                    this.applyCalendarHighlights();
                }
            }
        } catch (e) {
            console.error('[Gamification] Erro ao buscar dados:', e);
        } finally {
            this.gamificationLoading = false;
        }
    },

    async markReadingComplete(targetDate = null) {
        if (this.isMarkingComplete) return;

        const dateStr = targetDate || this.currentDate || new Date().toISOString().split('T')[0];
        this.isMarkingComplete = true;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const res = await fetch('/api/user/gamification/complete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ date: dateStr })
            });

            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    this.gamificationData = data.data;
                    this.applyCalendarHighlights();
                    console.log('[Gamification] Leitura concluída e marcada para:', dateStr);
                }
            }
        } catch (e) {
            console.error('[Gamification] Erro ao marcar leitura:', e);
        } finally {
            this.isMarkingComplete = false;
        }
    },

    applyCalendarHighlights() {
        if (!this.gamificationData || !this.gamificationData.completed_dates) return;

        const completedSet = new Set(this.gamificationData.completed_dates);

        // Aguarda renderização das tabelas de calendário no DOM
        setTimeout(() => {
            const dateButtons = document.querySelectorAll('.vanilla-calendar-day__btn');
            dateButtons.forEach(btn => {
                const dateAttr = btn.getAttribute('data-calendar-day');
                if (dateAttr && completedSet.has(dateAttr)) {
                    btn.classList.add('vc-day-completed');
                    if (!btn.querySelector('.vc-completed-dot')) {
                        const dot = document.createElement('span');
                        dot.className = 'vc-completed-dot absolute bottom-1 w-1.5 h-1.5 bg-emerald-500 rounded-full shadow-sm';
                        btn.style.position = 'relative';
                        btn.appendChild(dot);
                    }
                }
            });
        }, 100);
    }
});

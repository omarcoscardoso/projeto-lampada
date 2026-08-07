import confetti from 'canvas-confetti';

export const gamificationApp = () => ({
    gamificationData: null,
    gamificationLoading: false,
    isMarkingComplete: false,
    completedSessions: new Set(),

    initGamification() {
        this.fetchGamificationData();
    },

    async fetchGamificationData() {
        this.gamificationLoading = true;
        try {
            const res = await fetch('/api/user/gamification', {
                headers: {
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache',
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

    triggerReadingCompletionWithCelebration(targetDate = null) {
        const dateStr = targetDate || this.currentDate || new Date().toISOString().split('T')[0];

        // Evita disparar múltiplos fogos/requisições para a mesma data na mesma sessão
        if (this.completedSessions.has(dateStr)) return;
        this.completedSessions.add(dateStr);

        // 1. Estouro de Papéis de Festa / Fogos de Artifício (Confetes)
        this.fireConfetti();

        // 2. Incrementar a leitura e a ofensiva no backend
        this.markReadingComplete(dateStr);
    },

    fireConfetti() {
        try {
            const count = 250;
            const defaults = {
                origin: { y: 0.6 },
                zIndex: 999999
            };

            function fire(particleRatio, opts) {
                confetti({
                    ...defaults,
                    ...opts,
                    particleCount: Math.floor(count * particleRatio)
                });
            }

            fire(0.25, { spread: 26, startVelocity: 55 });
            fire(0.2, { spread: 60 });
            fire(0.35, { spread: 100, decay: 0.91, scalar: 0.8 });
            fire(0.1, { spread: 120, startVelocity: 25, decay: 0.92, scalar: 1.2 });
            fire(0.1, { spread: 120, startVelocity: 45 });
        } catch (e) {
            console.error('[Confetti] Erro ao disparar animação:', e);
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

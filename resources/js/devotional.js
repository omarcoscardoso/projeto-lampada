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
    isSpeaking: false,
    isPaused: false,
    showTtsSettings: false,
    ttsVoices: [],
    ttsVoiceIndex: 0,
    ttsRate: 0.9,
    ttsPitch: 0.5,
    ttsAnnounceVerses: false,
    isMusicPlaying: false,
    ttsAutoMusic: true,
    musicVolume: 0.20,

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
        this.initTtsVoices();
    },

    initTtsVoices() {
        if (!('speechSynthesis' in window)) { return; }

        const loadVoices = () => {
            const all = window.speechSynthesis.getVoices();
            this.ttsVoices = all.filter(v => v.lang.startsWith('pt'));
            if (this.ttsVoices.length === 0) {
                // Fallback: mostrar todas as vozes se não houver pt-BR
                this.ttsVoices = all;
            }
            this.ttsVoiceIndex = 0;
        };

        loadVoices();
        window.speechSynthesis.addEventListener('voiceschanged', loadVoices);
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
    },

    closeBibleModal() {
        this.stopTts();
        this.stopAmbientMusic();
        this.isAutoScrolling = false;
        this.showBibleModal = false;
    },

    toggleAmbientMusic() {
        if (this.isMusicPlaying) {
            this.stopAmbientMusic();
        } else {
            this.startAmbientMusic();
        }
    },

    startAmbientMusic() {
        if (this._audioCtx) { return; }

        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) {
            alert('Seu navegador não suporta áudio ambiente.');
            return;
        }

        const ctx = new AudioContext();
        this._audioCtx = ctx;
        this._musicNodes = [];

        // Master gain com fade-in suave (4s)
        const master = ctx.createGain();
        master.gain.setValueAtTime(0, ctx.currentTime);
        master.gain.linearRampToValueAtTime(this.musicVolume, ctx.currentTime + 4);
        master.connect(ctx.destination);
        this._musicMaster = master;

        // --- Reverb via feedback delay ---
        const delay = ctx.createDelay(2.0);
        const fbGain = ctx.createGain();
        const delayFilter = ctx.createBiquadFilter();
        delay.delayTime.value = 0.65;
        fbGain.gain.value = 0.42;
        delayFilter.type = 'lowpass';
        delayFilter.frequency.value = 700;
        delay.connect(delayFilter);
        delayFilter.connect(fbGain);
        fbGain.connect(delay); // loop de feedback

        const wetGain = ctx.createGain();
        wetGain.gain.value = 0.35;
        delayFilter.connect(wetGain);
        wetGain.connect(master);

        // Bus do PAD: seco vai pro master, molhado vai pro delay
        const padBus = ctx.createGain();
        padBus.gain.value = 1;
        padBus.connect(master);
        padBus.connect(delay);

        // --- PAD: Acorde Lá menor (A3, C4, E4, A4) ---
        const padNotes = [220, 261.63, 329.63, 440];
        padNotes.forEach((freq, i) => {
            // 2 osciladores por nota levemente desafinados = efeito chorus
            [-5, 5].forEach(detuneCents => {
                const osc = ctx.createOscillator();
                const oscGain = ctx.createGain();
                const filter = ctx.createBiquadFilter();

                osc.type = 'sine';
                osc.frequency.value = freq;
                osc.detune.value = detuneCents;

                filter.type = 'lowpass';
                filter.frequency.value = 1400;

                oscGain.gain.value = 0.045;

                // LFO individual para tremolo suave (ritmos diferentes por nota)
                const lfo = ctx.createOscillator();
                const lfoGain = ctx.createGain();
                lfo.type = 'sine';
                lfo.frequency.value = 0.06 + i * 0.02 + (detuneCents > 0 ? 0.01 : 0);
                lfoGain.gain.value = 0.012;
                lfo.connect(lfoGain);
                lfoGain.connect(oscGain.gain);
                lfo.start();

                osc.connect(filter);
                filter.connect(oscGain);
                oscGain.connect(padBus);
                osc.start();

                this._musicNodes.push(osc, lfo);
            });
        });

        // --- Binaural beats (requer fones de ouvido) ---
        // Esquerdo: 100 Hz | Direito: 108 Hz → batida de 8 Hz = onda alpha (foco relaxado)
        const merger = ctx.createChannelMerger(2);
        const binauralGain = ctx.createGain();
        binauralGain.gain.value = 0.07;
        merger.connect(binauralGain);
        binauralGain.connect(master);

        const leftOsc = ctx.createOscillator();
        leftOsc.type = 'sine';
        leftOsc.frequency.value = 100;

        const rightOsc = ctx.createOscillator();
        rightOsc.type = 'sine';
        rightOsc.frequency.value = 108;

        const leftGain = ctx.createGain();
        const rightGain = ctx.createGain();
        leftOsc.connect(leftGain);
        rightOsc.connect(rightGain);
        leftGain.connect(merger, 0, 0);  // canal esquerdo
        rightGain.connect(merger, 0, 1); // canal direito

        leftOsc.start();
        rightOsc.start();
        this._musicNodes.push(leftOsc, rightOsc);

        this.isMusicPlaying = true;
    },

    stopAmbientMusic() {
        if (!this._audioCtx) { return; }

        // Fade-out suave (2s) antes de destruir o contexto
        const gain = this._musicMaster;
        const ctx = this._audioCtx;
        gain.gain.cancelScheduledValues(ctx.currentTime);
        gain.gain.setValueAtTime(gain.gain.value, ctx.currentTime);
        gain.gain.linearRampToValueAtTime(0, ctx.currentTime + 2);

        setTimeout(() => {
            if (this._musicNodes) {
                this._musicNodes.forEach(node => { try { node.stop(); } catch (_) { } });
            }
            ctx.close();
            this._audioCtx = null;
            this._musicNodes = null;
            this._musicMaster = null;
            this.isMusicPlaying = false;
        }, 2200);
    },

    extractBibleText() {
        if (!this.bibleData) { return ''; }

        const parts = [];
        const addTestament = (testament) => {
            if (!testament || !testament.success) { return; }
            testament.chapters.forEach(chapter => {
                if (this.ttsAnnounceVerses) {
                    parts.push(`${testament.book_name} capítulo ${chapter.number}.`);
                }
                chapter.verses.forEach(verse => {
                    if (this.ttsAnnounceVerses) {
                        parts.push(`Versículo ${verse.number}. ${verse.text}`);
                    } else {
                        parts.push(verse.text);
                    }
                });
            });
        };

        addTestament(this.bibleData.old_testament);
        addTestament(this.bibleData.new_testament);

        return parts.join(' ');
    },

    // Dividir o texto em chunks por frase para contornar bug do Chrome com textos longos
    _splitIntoChunks(text, maxLength = 1000) {
        const sentences = text.match(/[^.!?]+[.!?]+/g) ?? [text];
        const chunks = [];
        let current = '';

        sentences.forEach(sentence => {
            if ((current + sentence).length > maxLength && current.length > 0) {
                chunks.push(current.trim());
                current = sentence;
            } else {
                current += sentence;
            }
        });

        if (current.trim().length > 0) {
            chunks.push(current.trim());
        }

        return chunks;
    },

    toggleTts() {
        if (!('speechSynthesis' in window)) {
            alert('Seu navegador não suporta leitura em voz alta.');
            return;
        }

        if (this.isSpeaking && !this.isPaused) {
            window.speechSynthesis.pause();
            this.isPaused = true;
            return;
        }

        if (this.isSpeaking && this.isPaused) {
            window.speechSynthesis.resume();
            this.isPaused = false;
            return;
        }

        const text = this.extractBibleText();
        if (!text) {
            console.warn('[TTS] Nenhum texto extraído do bibleData:', this.bibleData);
            return;
        }

        const chunks = this._splitIntoChunks(text);
        console.log('[TTS] Total de chunks:', chunks.length, '| Chars totais:', text.length);

        const speakChunk = (index) => {
            if (index >= chunks.length || !this.isSpeaking) {
                this.isSpeaking = false;
                this.isPaused = false;
                this._utterance = null;
                if (this._ttsKeepAlive) {
                    clearInterval(this._ttsKeepAlive);
                    this._ttsKeepAlive = null;
                }
                return;
            }

            this._utterance = new SpeechSynthesisUtterance(chunks[index]);
            this._utterance.lang = 'pt-BR';
            this._utterance.rate = this.ttsRate;
            this._utterance.pitch = this.ttsPitch;
            const selectedVoice = this.ttsVoices[this.ttsVoiceIndex] ?? null;
            if (selectedVoice) { this._utterance.voice = selectedVoice; }

            this._utterance.onend = () => { speakChunk(index + 1); };
            this._utterance.onerror = (e) => {
                // 'interrupted' e 'canceled' ocorrem ao pausar/parar — não são erros reais
                if (e.error === 'interrupted' || e.error === 'canceled') { return; }
                console.error('[TTS] Erro no chunk', index, ':', e.error);
                this.stopTts();
            };

            window.speechSynthesis.speak(this._utterance);
        };

        const startSpeaking = () => {
            window.speechSynthesis.cancel();
            this.isSpeaking = true;
            this.isPaused = false;

            if (this.ttsAutoMusic) {
                this.startAmbientMusic();
            }

            // Keep-alive: workaround para bug do Chrome que pausa silenciosamente após ~15s
            if (this._ttsKeepAlive) { clearInterval(this._ttsKeepAlive); }
            this._ttsKeepAlive = setInterval(() => {
                if (window.speechSynthesis.speaking && !this.isPaused) {
                    window.speechSynthesis.pause();
                    window.speechSynthesis.resume();
                }
            }, 10000);

            speakChunk(0);
        };

        if (this.ttsVoices.length === 0) {
            window.speechSynthesis.addEventListener('voiceschanged', startSpeaking, { once: true });
        } else {
            startSpeaking();
        }
    },

    stopTts() {
        if (this._ttsKeepAlive) {
            clearInterval(this._ttsKeepAlive);
            this._ttsKeepAlive = null;
        }
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
        }
        this.stopAmbientMusic();
        this.isSpeaking = false;
        this.isPaused = false;
        this._utterance = null;
    },
});


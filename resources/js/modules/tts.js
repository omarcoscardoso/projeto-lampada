export const ttsHandler = () => ({
    isSpeaking: false,
    isPaused: false,
    showTtsSettings: false,
    ttsAnnounceVerses: false,
    ttsAutoMusic: false,
    _audioElement: null,
    isTtsLoading: false,

    initTts() {
        // Vozes não precisam mais ser carregadas do navegador
    },

    extractBibleText() {
        if (!this.bibleData) { return ''; }

        const parts = [];
        const addTestament = (testament) => {
            if (!testament || !testament.success) { return; }
            testament.chapters.forEach(chapter => {
                parts.push(`${testament.book_name} capítulo ${chapter.number}.`);
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

    async toggleTts() {
        if (this.isSpeaking && !this.isPaused) {
            this._audioElement?.pause();
            this.stopAmbientMusic();
            this.isPaused = true;
            return;
        }

        if (this.isSpeaking && this.isPaused) {
            this._audioElement?.play();
            this.startAmbientMusic();
            this.isPaused = false;
            return;
        }

        if (this.isTtsLoading) return;

        const text = this.extractBibleText();
        if (!text) {
            console.warn('[TTS] Nenhum texto extraído do bibleData:', this.bibleData);
            return;
        }

        this.isTtsLoading = true;

        try {
            const dateStr = this.currentDate || new Date().toISOString().split('T')[0];
            const [, month, day] = dateStr.split('-');
            const formattedDate = `${month}/${day}`;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            /**
             * Google TTS suporta até 5000 caracteres. 
             * Para textos muito grandes, o ideal seria dividir em partes (chunks),
             * mas por enquanto vamos garantir que o truncamento não quebre a requisição.
             */
            const MAX_CHARS = 4800; // Google suporta até 5000 bytes
            const truncatedText = text.length > MAX_CHARS ? text.substring(0, MAX_CHARS) : text;

            if (text.length > MAX_CHARS) {
                console.warn('[TTS] Texto muito grande, truncado para os primeiros 4500 caracteres.');
            }

            const res = await fetch('/api/tts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    date: formattedDate,
                    text: truncatedText
                })
            });

            const data = await res.json();
            if (data.success && data.url) {
                console.log('[TTS] Audio URL recebida:', data.url);
                this.playAudioUrl(data.url);
            } else {
                console.error('[TTS] Erro da API:', data.message);
                alert('Não foi possível carregar o áudio.');
            }
        } catch (e) {
            console.error('[TTS] Erro ao fazer request para /api/tts', e);
            alert('Erro de conexão ao carregar áudio.');
        } finally {
            this.isTtsLoading = false;
        }
    },

    playAudioUrl(url) {
        if (this._audioElement) {
            this._audioElement.pause();
            this._audioElement.src = '';
            this._audioElement.load();
        }

        const timestampedUrl = `${url}${url.includes('?') ? '&' : '?'}t=${new Date().getTime()}`;
        this._audioElement = new Audio();

        // Evita problemas de CORS se o áudio estiver em outro domínio (ex: GCS)
        this._audioElement.crossOrigin = "anonymous";

        this._audioElement.onended = () => {
            this.stopTts();
        };

        this._audioElement.onerror = (e) => {
            console.error('[TTS] Erro no carregamento do áudio. Verifique se a URL é válida e acessível:', url, e);
            this.stopTts();
        };

        this._audioElement.src = timestampedUrl;
        this._audioElement.load();

        this._audioElement.play().then(() => {
            this.isSpeaking = true;
            this.isPaused = false;
            if (this.ttsAutoMusic) { this.startAmbientMusic(); }
        }).catch(err => {
            console.error('[TTS] Play failed', err);
            this.stopTts();
            alert('Erro ao reproduzir o áudio (política do navegador).');
        });
    },

    // Fallback: se usar o toggleAutoMusic do painel, afeta apenas o ambientMusic
    updateTtsRealtime() {
        // Sem uso, já que a voz não é mais sintetizada no navegador em tempo real
    },

    stopTts() {
        if (this._audioElement) {
            this._audioElement.pause();
            this._audioElement.currentTime = 0;
        }
        this.stopAmbientMusic();
        this.isSpeaking = false;
        this.isPaused = false;
    },
});
export const ttsHandler = () => ({
    isSpeaking: false,
    isPaused: false,
    showTtsSettings: false,
    ttsAnnounceVerses: false,
    ttsAutoMusic: false,
    _audioElement: null,
    _audioUrls: [],
    _currentAudioIndex: 0,
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

            const res = await fetch('/api/tts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    date: formattedDate,
                    text: text // Envia o texto completo
                })
            });

            const data = await res.json();
            if (data.success && data.urls && data.urls.length > 0) { // Espera um array de URLs
                console.log('[TTS] Audio URLs recebidas:', data.urls);
                this.playAudioPlaylist(data.urls); // Chama a nova função de playlist
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

    // Nova função para reproduzir uma lista de URLs de áudio sequencialmente
    playAudioPlaylist(urls) {
        this.stopTts(); // Para qualquer reprodução atual e reseta o estado

        if (!urls || urls.length === 0) {
            console.warn('[TTS] Nenhuma URL de áudio para reproduzir.');
            return;
        }

        this._audioUrls = urls;
        this._currentAudioIndex = 0;
        this.isSpeaking = true;
        this.isPaused = false;

        this._playCurrentAudioChunk();
    },

    _playCurrentAudioChunk() {
        if (this._currentAudioIndex >= this._audioUrls.length) {
            this.stopTts(); // Fim da playlist
            return;
        }

        const url = this._audioUrls[this._currentAudioIndex];

        if (this._audioElement) {
            this._audioElement.pause();
            this._audioElement = null;
        }

        // Como o nome do arquivo é um hash do conteúdo, ele é imutável. 
        // Podemos remover o timestamp para aproveitar o cache do navegador.
        this._audioElement = new Audio(url);

        this._audioElement.onended = () => {
            console.log(`[TTS] Chunk ${this._currentAudioIndex + 1} de ${this._audioUrls.length} finalizado.`);
            this._currentAudioIndex++;
            this._playCurrentAudioChunk(); // Reproduz o próximo chunk
        };

        this._audioElement.onerror = (e) => {
            console.error(`[TTS] Erro no carregamento do chunk de áudio ${this._currentAudioIndex + 1}:`, url, e);

            // Tenta verificar se o erro é um 404 via fetch para logar melhor
            fetch(url, { method: 'HEAD' }).then(res => {
                if (!res.ok) {
                    console.error(`[TTS] Arquivo MP3 não encontrado ou inacessível (Status: ${res.status})`);
                }
            }).catch(() => { });

            this.stopTts(); // Para a playlist inteira em caso de erro
            alert('Erro ao carregar um trecho do áudio.');
        };

        this._audioElement.play().then(() => {
            console.log(`[TTS] Iniciando chunk ${this._currentAudioIndex + 1} de ${this._audioUrls.length}.`);
            if (this.ttsAutoMusic && this._currentAudioIndex === 0) { // Inicia a música ambiente apenas para o primeiro chunk
                this.startAmbientMusic();
            }
        }).catch(err => {
            console.error('[TTS] Falha na reprodução do chunk', this._currentAudioIndex + 1, err);
            this.stopTts();
            alert('Erro ao reproduzir o áudio (política do navegador).');
        });
    },

    stopTts() {
        if (this._audioElement) {
            this._audioElement.pause();
            this._audioElement.currentTime = 0;
        }
        this.stopAmbientMusic();
        this.isSpeaking = false;
        this.isPaused = false;
        this._audioUrls = []; // Reseta a playlist
        this._currentAudioIndex = 0; // Reseta o índice
    },

    // Funções auxiliares para música ambiente (assumindo que existam em outro lugar)
    startAmbientMusic() {
        console.log('[TTS] Música ambiente iniciada.');
    },
    stopAmbientMusic() {
        console.log('[TTS] Música ambiente parada.');
    },
});
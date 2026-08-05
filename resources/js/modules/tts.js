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

    extractBibleBlocks() {
        if (!this.bibleData) { return []; }

        const blocks = [];
        const processTestament = (testament, testamentType) => {
            if (!testament || !testament.success || !testament.chapters) { return; }

            testament.chapters.forEach(chapter => {
                const parts = [];
                parts.push(`${testament.book_name} o capítulo ${chapter.number}.`);

                let startVerse = null;
                let endVerse = null;

                if (chapter.verses && chapter.verses.length > 0) {
                    startVerse = chapter.verses[0].number;
                    endVerse = chapter.verses[chapter.verses.length - 1].number;

                    chapter.verses.forEach(verse => {
                        if (this.ttsAnnounceVerses) {
                            parts.push(`Versículo ${verse.number}. ${verse.text}`);
                        } else {
                            parts.push(verse.text);
                        }
                    });
                }

                blocks.push({
                    testament: testamentType,
                    book_name: testament.book_name,
                    book_abbrev: testament.book_abbrev || testament.book_name.substring(0, 3).toLowerCase(),
                    chapter: chapter.number,
                    start_verse: startVerse,
                    end_verse: endVerse,
                    text: parts.join(' ')
                });
            });
        };

        processTestament(this.bibleData.old_testament, 'old');
        processTestament(this.bibleData.new_testament, 'new');

        return blocks;
    },

    async toggleTts() {
        if (this.isSpeaking && !this.isPaused) {
            this._audioElement?.pause();
            if (this.ttsAutoMusic) this.stopAmbientMusic();
            this.isPaused = true;
            return;
        }

        if (this.isSpeaking && this.isPaused) {
            this._audioElement?.play();
            if (this.ttsAutoMusic) this.startAmbientMusic();
            this.isPaused = false;
            return;
        }

        if (this.isTtsLoading) return;

        const blocks = this.extractBibleBlocks();
        if (!blocks || blocks.length === 0) {
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
                    blocks: blocks // Envia os blocos estruturados
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
            this.markReadingComplete();
            this.stopTts(); // Fim da playlist
            return;
        }

        const url = this._audioUrls[this._currentAudioIndex];

        if (this._audioElement) {
            this._audioElement.pause();
            this._audioElement = null;
        }

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
        if (this.ttsAutoMusic) this.stopAmbientMusic();
        this.isSpeaking = false;
        this.isPaused = false;
        this._audioUrls = []; // Reseta a playlist
        this._currentAudioIndex = 0; // Reseta o índice
    },
});
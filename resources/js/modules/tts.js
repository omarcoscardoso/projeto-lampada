import { TTSHighlightManager, BibleTokenizer } from './tts-highlight';

const highlightManager = new TTSHighlightManager();


export const ttsHandler = () => ({
    isSpeaking: false,
    isPaused: false,
    showTtsSettings: false,
    ttsAnnounceVerses: false,
    ttsAutoMusic: false,
    highlightFollow: true,
    _audioElement: null,
    _audioUrls: [],
    _currentAudioIndex: 0,
    isTtsLoading: false,
    _rafId: null,
    _currentBlocks: [],

    initTts() {
        this.highlightManager = highlightManager;
    },


    toggleHighlightFollow() {
        this.highlightFollow = !this.highlightFollow;
        highlightManager.highlightFollow = this.highlightFollow;
    },

    extractBibleBlocks() {
        if (!this.bibleData) { return []; }

        // Garante que o texto bíblico possui tokens indexados
        BibleTokenizer.processBibleData(this.bibleData);

        const blocks = [];
        const processTestament = (testament, testamentType) => {
            if (!testament || !testament.success || !testament.chapters) { return; }

            testament.chapters.forEach(chapter => {
                const parts = [];
                const blockTokens = [];
                parts.push(`${testament.book_name} o capítulo ${chapter.number}.`);

                let startVerse = null;
                let endVerse = null;

                if (chapter.verses && chapter.verses.length > 0) {
                    startVerse = chapter.verses[0].number;
                    endVerse = chapter.verses[chapter.verses.length - 1].number;

                    chapter.verses.forEach(verse => {
                        if (verse.tokens) {
                            blockTokens.push(...verse.tokens);
                        }
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
                    text: parts.join(' '),
                    tokens: blockTokens,
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
            this._stopSyncLoop();
            return;
        }

        if (this.isSpeaking && this.isPaused) {
            this._audioElement?.play();
            if (this.ttsAutoMusic) this.startAmbientMusic();
            this.isPaused = false;
            this._startSyncLoop();
            return;
        }

        if (this.isTtsLoading) return;

        const blocks = this.extractBibleBlocks();
        if (!blocks || blocks.length === 0) {
            console.warn('[TTS] Nenhum texto extraído do bibleData:', this.bibleData);
            return;
        }

        this._currentBlocks = blocks;
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
                    blocks: blocks
                })
            });

            const data = await res.json();
            if (data.success && data.urls && data.urls.length > 0) {
                console.log('[TTS] Audio URLs recebidas:', data.urls);
                this.playAudioPlaylist(data.urls);
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

    playAudioPlaylist(urls) {
        const savedBlocks = (this._currentBlocks && this._currentBlocks.length)
            ? this._currentBlocks
            : this.extractBibleBlocks();

        this.stopTts();

        if (!urls || urls.length === 0) {
            console.warn('[TTS] Nenhuma URL de áudio para reproduzir.');
            return;
        }

        this._currentBlocks = savedBlocks;
        this._audioUrls = urls;
        this._currentAudioIndex = 0;
        this.isSpeaking = true;
        this.isPaused = false;

        // Garante que as referências do DOM estejam atualizadas
        const container = this.$refs?.bibleContainer || document.getElementById('bibleContainer');
        if (container) {
            highlightManager.setContainerRef(container);
        }
        highlightManager.cacheDOMReferences();
        highlightManager.highlightFollow = this.highlightFollow;

        this._playCurrentAudioChunk();
    },

    _playCurrentAudioChunk() {
        if (this._currentAudioIndex >= this._audioUrls.length) {
            this.triggerReadingCompletionWithCelebration();
            this.stopTts();
            return;
        }

        const url = this._audioUrls[this._currentAudioIndex];

        if (this._audioElement) {
            this._audioElement.pause();
            this._audioElement = null;
        }

        this._stopSyncLoop();

        this._audioElement = new Audio(url);

        this._audioElement.onended = () => {
            console.log(`[TTS] Chunk ${this._currentAudioIndex + 1} de ${this._audioUrls.length} finalizado.`);
            this._stopSyncLoop();
            this._currentAudioIndex++;
            this._playCurrentAudioChunk();
        };

        this._audioElement.onerror = (e) => {
            console.error(`[TTS] Erro no carregamento do chunk de áudio ${this._currentAudioIndex + 1}:`, url, e);

            fetch(url, { method: 'HEAD' }).then(res => {
                if (!res.ok) {
                    console.error(`[TTS] Arquivo MP3 não encontrado ou inacessível (Status: ${res.status})`);
                }
            }).catch(() => { });

            this.stopTts();
            alert('Erro ao carregar um trecho do áudio.');
        };

        this._audioElement.play().then(() => {
            console.log(`[TTS] Iniciando chunk ${this._currentAudioIndex + 1} de ${this._audioUrls.length}.`);
            if (this.ttsAutoMusic && this._currentAudioIndex === 0) {
                this.startAmbientMusic();
            }
            this._startSyncLoop();
        }).catch(err => {
            console.error('[TTS] Falha na reprodução do chunk', this._currentAudioIndex + 1, err);
            this.stopTts();
            alert('Erro ao reproduzir o áudio (política do navegador).');
        });
    },

    _startSyncLoop() {
        this._stopSyncLoop();

        let blockTokens = [];
        if (this._currentBlocks && this._currentBlocks[this._currentAudioIndex]) {
            blockTokens = this._currentBlocks[this._currentAudioIndex].tokens || [];
        }
        if (!blockTokens.length && this._currentBlocks) {
            this._currentBlocks.forEach(b => {
                if (b.tokens) blockTokens.push(...b.tokens);
            });
        }

        if (!blockTokens.length) {
            console.warn('[TTS-Sync] Nenhum token encontrado para sincronização.');
            return;
        }

        console.log(`[TTS-Sync] Loop ativado para o chunk ${this._currentAudioIndex + 1} com ${blockTokens.length} tokens.`);

        const loop = () => {
            if (this._audioElement && !this._audioElement.paused && this._audioElement.duration > 0) {
                const duration = this._audioElement.duration;
                const currentTime = this._audioElement.currentTime;
                const progress = Math.min(Math.max(currentTime / duration, 0), 1);

                const tokenIndex = Math.min(
                    Math.floor(progress * blockTokens.length),
                    blockTokens.length - 1
                );

                const activeToken = blockTokens[tokenIndex];
                if (activeToken) {
                    highlightManager.setActiveToken(activeToken.id);
                }
            }

            if (this.isSpeaking && !this.isPaused) {
                this._rafId = requestAnimationFrame(loop);
            }
        };

        this._rafId = requestAnimationFrame(loop);
    },

    _stopSyncLoop() {
        if (this._rafId !== null) {
            cancelAnimationFrame(this._rafId);
            this._rafId = null;
        }
    },

    stopTts() {
        this._stopSyncLoop();

        if (this._audioElement) {
            this._audioElement.pause();
            this._audioElement.currentTime = 0;
        }
        if (this.ttsAutoMusic) this.stopAmbientMusic();
        
        highlightManager.clearAllHighlights();

        this.isSpeaking = false;
        this.isPaused = false;
        this._audioUrls = [];
        this._currentAudioIndex = 0;
        this._currentBlocks = [];
    },
});
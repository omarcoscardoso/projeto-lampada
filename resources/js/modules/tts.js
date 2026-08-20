import { TTSHighlightManager } from './tts-highlight';

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

        const blocks = [];
        const processTestament = (testament, testamentType) => {
            if (!testament || !testament.success || !testament.chapters) { return; }

            testament.chapters.forEach(chapter => {
                const parts = [];
                const versesInfo = [];

                const headerText = `${testament.book_name} o capítulo ${chapter.number}.`;
                parts.push(headerText);

                let totalVersesCharLength = 0;

                if (chapter.verses && chapter.verses.length > 0) {
                    chapter.verses.forEach(verse => {
                        const verseText = verse.text;
                        const verseCharLength = verseText.length;
                        const verseKey = `${chapter.number}.${verse.number}`;

                        versesInfo.push({
                            verseKey,
                            number: verse.number,
                            text: verseText,
                            charStart: totalVersesCharLength,
                            charEnd: totalVersesCharLength + verseCharLength,
                            length: verseCharLength,
                        });

                        totalVersesCharLength += verseCharLength;
                        parts.push(verseText);
                    });
                }

                versesInfo.forEach(v => {
                    v.ratioStart = totalVersesCharLength > 0 ? (v.charStart / totalVersesCharLength) : 0;
                    v.ratioEnd = totalVersesCharLength > 0 ? (v.charEnd / totalVersesCharLength) : 1;
                });

                const fullText = parts.join(' ');
                const headerLength = headerText.length;
                const totalLength = fullText.length;
                const headerRatio = totalLength > 0 ? (headerLength / totalLength) : 0;

                blocks.push({
                    testament: testamentType,
                    book_name: testament.book_name,
                    book_abbrev: testament.book_abbrev || testament.book_name.substring(0, 3).toLowerCase(),
                    chapter: chapter.number,
                    start_verse: chapter.verses?.[0]?.number ?? null,
                    end_verse: chapter.verses?.[chapter.verses.length - 1]?.number ?? null,
                    text: fullText,
                    headerText,
                    headerRatio,
                    verses: versesInfo,
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

        let currentBlock = null;
        let verses = [];
        if (this._currentBlocks && this._currentBlocks[this._currentAudioIndex]) {
            currentBlock = this._currentBlocks[this._currentAudioIndex];
            verses = currentBlock.verses || [];
        }
        if (!verses.length && this._currentBlocks) {
            this._currentBlocks.forEach(b => {
                if (b.verses) verses.push(...b.verses);
            });
        }

        if (!verses.length) {
            console.warn('[TTS-Sync] Nenhum versículo encontrado para sincronização.');
            return;
        }

        const headerRatio = currentBlock?.headerRatio || 0;
        console.log(`[TTS-Sync] Loop ativado para o chunk ${this._currentAudioIndex + 1} com ${verses.length} versículos. Header Offset: ${(headerRatio * 100).toFixed(1)}%`);

        const loop = () => {
            if (this._audioElement && !this._audioElement.paused && this._audioElement.duration > 0) {
                const duration = this._audioElement.duration;
                const currentTime = this._audioElement.currentTime;
                const overallProgress = Math.min(Math.max(currentTime / duration, 0), 1);

                if (overallProgress < headerRatio) {
                    highlightManager.clearAllHighlights();
                } else {
                    const versesProgress = Math.min(Math.max((overallProgress - headerRatio) / (1 - headerRatio), 0), 1);

                    const activeVerse = verses.find(v => versesProgress >= v.ratioStart && versesProgress <= v.ratioEnd) || verses[verses.length - 1];

                    if (activeVerse) {
                        highlightManager.setActiveVerse(activeVerse.verseKey);
                    }
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
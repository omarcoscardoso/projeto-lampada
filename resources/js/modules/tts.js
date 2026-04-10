export const ttsHandler = () => ({
    isSpeaking: false,
    isPaused: false,
    showTtsSettings: false,
    ttsVoices: [],
    ttsVoiceIndex: 0,
    ttsRate: 1,
    ttsPitch: 1.1,
    ttsAnnounceVerses: false,
    ttsAutoMusic: true,
    _ttsKeepAlive: null,
    _utterance: null,
    _ttsChunks: [],
    _currentChunkIndex: 0,

    initTts() {
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

    speak(text) {
        if (!text) return;
        this._ttsChunks = this._splitIntoChunks(text);
        this._currentChunkIndex = 0;
        this._startSpeechSequence();
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

    // Dividir o texto em chunks por frase para contornar bug do Chrome com textos longos
    _splitIntoChunks(text, maxLength = 400) {
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
            this.stopAmbientMusic();
            this.isPaused = true;
            return;
        }

        if (this.isSpeaking && this.isPaused) {
            window.speechSynthesis.resume();
            this.startAmbientMusic();
            this.isPaused = false;
            return;
        }

        const text = this.extractBibleText();
        if (!text) {
            console.warn('[TTS] Nenhum texto extraído do bibleData:', this.bibleData);
            return;
        }

        this._ttsChunks = this._splitIntoChunks(text);
        this._currentChunkIndex = 0;
        this._startSpeechSequence();
    },

    _startSpeechSequence() {
        // Reset fundamental para Mobile: cancela e força o 'resume' para destravar a engine
        window.speechSynthesis.cancel();
        window.speechSynthesis.resume();

        this.isSpeaking = true;
        this.isPaused = false;

        if (this.ttsAutoMusic) { this.startAmbientMusic(); }

        if (this._ttsKeepAlive) { clearInterval(this._ttsKeepAlive); }
        
        // Intervalo de keep-alive: essencial para mobile. 
        // Reduzido para 5s para garantir que o processo não seja morto pelo SO.
        this._ttsKeepAlive = setInterval(() => {
            if (window.speechSynthesis.speaking && !this.isPaused) {
                window.speechSynthesis.pause();
                window.speechSynthesis.resume();
            }
            console.log('[TTS] Keep-alive: CHEGUEI AQUI!!!!');
        }, 4000);

        this.speakChunk(0);
    },

    speakChunk(index) {
        if (index >= this._ttsChunks.length || !this.isSpeaking) {
            if (index >= this._ttsChunks.length) {
                this.stopTts();
            }
            return;
        }

        this._currentChunkIndex = index;
        this._utterance = new SpeechSynthesisUtterance(this._ttsChunks[index]);
        this._utterance.lang = 'pt-BR';
        this._utterance.rate = this.ttsRate;
        this._utterance.pitch = this.ttsPitch;

        const selectedVoice = this.ttsVoices[this.ttsVoiceIndex] ?? null;
        if (selectedVoice) { this._utterance.voice = selectedVoice; }

        this._utterance.onend = () => {
            if (this.isSpeaking && !this.isPaused) {
                this.speakChunk(index + 1);
            }
        };

        this._utterance.onerror = (e) => {
            if (e.error === 'interrupted' || e.error === 'canceled') { return; }
            console.error('[TTS] Erro no chunk', index, ':', e.error);
            this.stopTts();
        };

        window.speechSynthesis.speak(this._utterance);
    },

    updateTtsRealtime() {
        if (!this.isSpeaking) return;

        // Reiniciar o chunk atual com as novas configurações (pitch, rate, voice)
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            this.speakChunk(this._currentChunkIndex);
        }
    },
    
    stopTts() {
        if (this._ttsKeepAlive) {
            clearInterval(this._ttsKeepAlive);
            this._ttsKeepAlive = null;
        }
        if (window.speechSynthesis) {
            window.speechSynthesis.cancel();
        }
        this.stopAmbientMusic();
        this.isSpeaking = false;
        this.isPaused = false;
        this._utterance = null;
    }, 

});
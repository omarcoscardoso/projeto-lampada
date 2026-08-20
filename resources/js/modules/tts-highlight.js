/**
 * Módulo de Tokenização Bíblica e Gerenciamento de Highlight Visual do TTS
 * Projeto Lâmpada - Suporte a Web Speech API e Áudio MP3
 */

export class BibleTokenizer {
    /**
     * Processa a estrutura de dados da Bíblia (bibleData) e adiciona os tokens indexados.
     * @param {Object} bibleData
     * @returns {{ allTokens: Array }}
     */
    static processBibleData(bibleData) {
        if (!bibleData) return { allTokens: [] };

        let globalTokenId = 0;
        let globalCharOffset = 0;
        const allTokens = [];

        const processTestament = (testament, testamentType) => {
            if (!testament || !testament.success || !testament.chapters) return;

            testament.chapters.forEach(chapter => {
                if (!chapter.verses) return;

                chapter.verses.forEach(verse => {
                    const verseKey = `${chapter.number}.${verse.number}`;
                    const tokens = [];

                    // Separa por palavras preservando os delimitadores de espaço
                    const parts = verse.text.split(/(\s+)/);

                    parts.forEach(part => {
                        if (!part || /^\s+$/.test(part)) {
                            globalCharOffset += part.length;
                            return;
                        }

                        const cleanWord = part.replace(/[^\p{L}\p{N}]/gu, '');
                        const charStart = globalCharOffset;
                        const charEnd = globalCharOffset + part.length;

                        const token = {
                            id: globalTokenId++,
                            verseKey,
                            testament: testamentType,
                            chapter: chapter.number,
                            verseNumber: verse.number,
                            rawText: part,
                            cleanWord: cleanWord || part,
                            charStart,
                            charEnd,
                        };

                        tokens.push(token);
                        allTokens.push(token);
                        globalCharOffset += part.length;
                    });

                    verse.tokens = tokens;
                });
            });
        };

        processTestament(bibleData.old_testament, 'old');
        processTestament(bibleData.new_testament, 'new');

        return {
            allTokens,
        };
    }
}

export class TTSHighlightManager {
    constructor() {
        this.tokenDomMap = new Map();
        this.verseDomMap = new Map();
        this.currentActiveTokenEl = null;
        this.currentActiveVerseEl = null;
        this.highlightFollow = true; // Ativado por padrão
        this.scrollDebounceTimer = null;
        this.containerRef = null;
    }

    setContainerRef(containerEl) {
        this.containerRef = containerEl;
    }

    /**
     * Mapeia os elementos do DOM em O(1) com fallback para document completo
     */
    cacheDOMReferences() {
        this.tokenDomMap.clear();
        this.verseDomMap.clear();

        let spans = [];
        if (this.containerRef && document.body.contains(this.containerRef)) {
            spans = Array.from(this.containerRef.querySelectorAll('span[data-t]'));
        }
        if (!spans.length) {
            spans = Array.from(document.querySelectorAll('span[data-t]'));
        }

        spans.forEach(span => {
            const tokenId = parseInt(span.getAttribute('data-t'), 10);
            if (!isNaN(tokenId)) {
                this.tokenDomMap.set(tokenId, span);
            }
        });

        let verses = [];
        if (this.containerRef && document.body.contains(this.containerRef)) {
            verses = Array.from(this.containerRef.querySelectorAll('[data-v]'));
        }
        if (!verses.length) {
            verses = Array.from(document.querySelectorAll('[data-v]'));
        }

        verses.forEach(verse => {
            const verseKey = verse.getAttribute('data-v');
            if (verseKey && !this.verseDomMap.has(verseKey)) {
                this.verseDomMap.set(verseKey, verse);
            }
        });

        console.log(`[TTS-Highlight] Mapeados ${this.tokenDomMap.size} tokens e ${this.verseDomMap.size} versículos no DOM.`);
    }

    /**
     * Aplica o destaque na palavra e no versículo ativo
     * @param {number} tokenId 
     */
    setActiveToken(tokenId) {
        if (this.tokenDomMap.size === 0) {
            this.cacheDOMReferences();
        }

        let nextTokenEl = this.tokenDomMap.get(tokenId);

        // Fallback: se não encontrou o elemento no cache, recarrega referências do DOM
        if (!nextTokenEl) {
            this.cacheDOMReferences();
            nextTokenEl = this.tokenDomMap.get(tokenId);
        }

        if (!nextTokenEl || nextTokenEl === this.currentActiveTokenEl) return;

        // 1. Remove destaque do token anterior
        if (this.currentActiveTokenEl) {
            this.currentActiveTokenEl.removeAttribute('data-playing');
        }

        // 2. Aplica destaque no token atual
        nextTokenEl.setAttribute('data-playing', 'true');
        this.currentActiveTokenEl = nextTokenEl;

        // 3. Destaca o versículo pai
        const verseKey = nextTokenEl.getAttribute('data-v');
        if (verseKey) {
            this.setActiveVerse(verseKey);
        }

        // 4. Autoscroll suave
        if (this.highlightFollow) {
            this.scrollToElementIfNeeded(nextTokenEl);
        }
    }

    setActiveVerse(verseKey) {
        const nextVerseEl = this.verseDomMap.get(verseKey);
        if (!nextVerseEl || nextVerseEl === this.currentActiveVerseEl) return;

        if (this.currentActiveVerseEl) {
            this.currentActiveVerseEl.classList.remove('active-verse');
            this.currentActiveVerseEl.removeAttribute('data-verse-playing');
        }

        nextVerseEl.classList.add('active-verse');
        nextVerseEl.setAttribute('data-verse-playing', 'true');
        this.currentActiveVerseEl = nextVerseEl;
    }

    scrollToElementIfNeeded(el) {
        const container = this.containerRef || el.closest('#bibleContainer, [x-ref="bibleContainer"]') || window;

        const rect = el.getBoundingClientRect();
        const containerRect = (container !== window && container.getBoundingClientRect)
            ? container.getBoundingClientRect()
            : { top: 0, bottom: window.innerHeight };

        const upperLimit = containerRect.top + (containerRect.bottom - containerRect.top) * 0.3;
        const lowerLimit = containerRect.top + (containerRect.bottom - containerRect.top) * 0.7;

        if (rect.top < upperLimit || rect.bottom > lowerLimit) {
            if (this.scrollDebounceTimer !== null) return;

            this.scrollDebounceTimer = window.setTimeout(() => {
                el.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                    inline: 'nearest'
                });
                this.scrollDebounceTimer = null;
            }, 100);
        }
    }

    clearAllHighlights() {
        if (this.currentActiveTokenEl) {
            this.currentActiveTokenEl.removeAttribute('data-playing');
            this.currentActiveTokenEl = null;
        }
        if (this.currentActiveVerseEl) {
            this.currentActiveVerseEl.classList.remove('active-verse');
            this.currentActiveVerseEl.removeAttribute('data-verse-playing');
            this.currentActiveVerseEl = null;
        }
        if (this.scrollDebounceTimer !== null) {
            clearTimeout(this.scrollDebounceTimer);
            this.scrollDebounceTimer = null;
        }
    }
}

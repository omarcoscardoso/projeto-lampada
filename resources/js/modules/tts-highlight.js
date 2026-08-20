/**
 * Módulo de Gerenciamento de Highlight Visual de Versículos do TTS
 * Projeto Lâmpada - Destaque por Versículo Inteiro
 */

export class TTSHighlightManager {
    constructor() {
        this.verseDomMap = new Map();
        this.currentActiveVerseEl = null;
        this.highlightFollow = true; // Ativado por padrão
        this.scrollDebounceTimer = null;
        this.containerRef = null;
    }

    setContainerRef(containerEl) {
        this.containerRef = containerEl;
    }

    /**
     * Mapeia os elementos de versículo [data-v] do DOM em O(1)
     */
    cacheDOMReferences() {
        this.verseDomMap.clear();

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

        console.log(`[TTS-Highlight] Mapeados ${this.verseDomMap.size} versículos no DOM.`);
    }

    /**
     * Destaca o versículo ativo inteiro e executa o autoscroll
     * @param {string} verseKey ex: "119.1"
     */
    setActiveVerse(verseKey) {
        if (this.verseDomMap.size === 0) {
            this.cacheDOMReferences();
        }

        let nextVerseEl = this.verseDomMap.get(verseKey);

        if (!nextVerseEl) {
            this.cacheDOMReferences();
            nextVerseEl = this.verseDomMap.get(verseKey);
        }

        if (!nextVerseEl || nextVerseEl === this.currentActiveVerseEl) return;

        // 1. Remove destaque do versículo anterior
        if (this.currentActiveVerseEl) {
            this.currentActiveVerseEl.classList.remove('active-verse');
            this.currentActiveVerseEl.removeAttribute('data-playing');
        }

        // 2. Aplica destaque no versículo atual
        nextVerseEl.classList.add('active-verse');
        nextVerseEl.setAttribute('data-playing', 'true');
        this.currentActiveVerseEl = nextVerseEl;

        // 3. Autoscroll suave para o versículo ativo
        if (this.highlightFollow) {
            this.scrollToElementIfNeeded(nextVerseEl);
        }
    }

    scrollToElementIfNeeded(el) {
        const container = this.containerRef || el.closest('#bibleContainer, [x-ref="bibleContainer"]') || window;

        const rect = el.getBoundingClientRect();
        const containerRect = (container !== window && container.getBoundingClientRect)
            ? container.getBoundingClientRect()
            : { top: 0, bottom: window.innerHeight };

        const upperLimit = containerRect.top + (containerRect.bottom - containerRect.top) * 0.25;
        const lowerLimit = containerRect.top + (containerRect.bottom - containerRect.top) * 0.75;

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
        if (this.currentActiveVerseEl) {
            this.currentActiveVerseEl.classList.remove('active-verse');
            this.currentActiveVerseEl.removeAttribute('data-playing');
            this.currentActiveVerseEl = null;
        }
        if (this.scrollDebounceTimer !== null) {
            clearTimeout(this.scrollDebounceTimer);
            this.scrollDebounceTimer = null;
        }
    }
}

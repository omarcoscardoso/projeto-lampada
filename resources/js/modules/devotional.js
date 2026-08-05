export const devotionalApp = () => ({
    showBibleModal: false,
    bibleLoading: false,
    bibleData: null,
    isAutoScrolling: false,
    scrollPos: 0,
    error: null,

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

                this.scrollPos += 0.25;
                container.scrollTop = this.scrollPos;

                if (Math.ceil(this.scrollPos + container.clientHeight) >= container.scrollHeight - 10) {
                    this.isAutoScrolling = false;
                    this.markReadingComplete();
                    return;
                }
                requestAnimationFrame(scrollStep);
            };
            requestAnimationFrame(scrollStep);
        }
    },

    checkBibleScrollEnd() {
        const container = this.$refs.bibleContainer;
        if (!container) return;

        if (Math.ceil(container.scrollTop + container.clientHeight) >= container.scrollHeight - 40) {
            this.markReadingComplete();
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
        this.stopAmbientMusic(); // Corrigido para acessar o método do componente Alpine
        this.isAutoScrolling = false;
        this.showBibleModal = false;
    }

});

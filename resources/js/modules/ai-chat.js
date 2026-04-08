export const aiChat = () => ({
    showAiChat: false,
    messages: [],
    userInput: '',
    isAiLoading: false,

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
    }
});
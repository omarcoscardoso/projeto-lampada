export const whatsappHandler = () => ({
    shareOnWhatsApp(data = null) {
        // Se 'data' for um Evento (chamada sem parênteses), usamos o dado do componente
        const devotional = (data && typeof data === 'object' && 'reference_old_testament' in data) 
            ? data 
            : this.devotionalData;

        if (!devotional || !devotional.reference_old_testament) return;

        const message = devotional.whatsapp_message ||
            `*${devotional.reference_old_testament}*\n${this.formatForWhatsApp(devotional.content_old_testament)}\n\n*${devotional.reference_new_testament}*\n${this.formatForWhatsApp(devotional.content_new_testament)}`;

        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message.trim())}`;
        window.open(whatsappUrl, '_blank');
    },

    formatForWhatsApp(html) {
        if (!html) return '';
        let formatted = html
            .replace(/<br\s*\/?>/gi, '\n')
            .replace(/<h[1-6][^>]*>/gi, '\n\n*')
            .replace(/<\/h[1-6]>/gi, '*\n')
            .replace(/<(p|div|section)[^>]*>/gi, '\n')
            .replace(/<\/(p|div|section)>/gi, '\n')
            .replace(/<li[^>]*>/gi, '\n- ')
            .replace(/<\/li>/gi, '\n')
            .replace(/<\/?(ul|ol)[^>]*>/gi, '\n')
            .replace(/<(b|strong)[^>]*>/gi, '*')
            .replace(/<\/(b|strong)>/gi, '*')
            .replace(/<(i|em)[^>]*>/gi, '_')
            .replace(/<\/(i|em)>/gi, '_')
            .replace(/<(s|strike|del)[^>]*>/gi, '~')
            .replace(/<\/(s|strike|del)>/gi, '~')
            .replace(/<(code|pre)[^>]*>/gi, '```')
            .replace(/<\/(code|pre)>/gi, '```');

        let tmp = document.createElement("DIV");
        tmp.innerHTML = formatted;
        let text = tmp.textContent || tmp.innerText || "";

        text = text.replace(/(-)\s*[\r\n]+/g, '$1 ');
        text = text.split('\n').map(line => {
            const trimmed = line.trim();
            if (trimmed.startsWith('-')) return '- ' + trimmed.substring(1).trim();
            return trimmed;
        }).join('\n');

        text = text.replace(/\n+(- )/g, '\n$1');
        return text.replace(/\n{3,}/g, '\n\n').trim();
    }
});
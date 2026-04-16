export const landingApp = () => ({

    shareLampada() {
        if (navigator.share) {
            navigator.share({
                title: 'Projeto Lâmpada | IPR Viamão',
                text: 'Una-se a nós na jornada de ler a Bíblia completa em um ano! Projeto Lâmpada: Toda a Escritura para Toda a Vida.',
                url: window.location.href
            })
                .then(() => console.log('Compartilhado com sucesso'))
                .catch((error) => console.log('Erro ao compartilhar', error));
        } else {
            // Fallback para Desktop ou navegadores sem suporte
            navigator.clipboard.writeText(window.location.href);
            alert('Link copiado para a área de transferência! Compartilhe com seus amigos.');
        }
    }

});
import './bootstrap';

import './bootstrap';
import Alpine from 'alpinejs';
import devotionalApp from './devotional';

// Import Vanilla Calendar CSS
import 'vanilla-calendar-pro/styles/index.css';
import 'vanilla-calendar-pro/styles/themes/light.css';
import 'vanilla-calendar-pro/styles/themes/dark.css';

// Registrar componentes Alpine
window.Alpine = Alpine;
Alpine.data('devotionalApp', devotionalApp);

// Iniciar Alpine
Alpine.start();

// PWA Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => console.log('[SW] OK'))
            .catch(err => console.log('[SW] KO'));
    });
}

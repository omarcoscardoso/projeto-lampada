import './bootstrap';
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import devotionalApp from './devotional';

// Import Vanilla Calendar CSS
import 'vanilla-calendar-pro/styles/index.css';
import 'vanilla-calendar-pro/styles/themes/light.css';
import 'vanilla-calendar-pro/styles/themes/dark.css';

Alpine.data('devotionalApp', devotionalApp);

Livewire.start();

// PWA Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => console.log('[SW] OK'))
            .catch(err => console.log('[SW] KO'));
    });
}

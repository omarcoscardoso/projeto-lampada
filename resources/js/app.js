// import './bootstrap';
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import { devotionalApp } from './modules/devotional';
import { calendarApp } from './modules/calendar';
import { ambientMusic } from './modules/ambient-music';
import { ttsHandler } from './modules/tts';
import { aiChat } from './modules/ai-chat';
import { whatsappHandler } from './modules/whatsapp';
import { landingApp } from './modules/landing';
import { gamificationApp } from './modules/gamification';

// Import Vanilla Calendar CSS
import 'vanilla-calendar-pro/styles/index.css';
import 'vanilla-calendar-pro/styles/themes/light.css';
import 'vanilla-calendar-pro/styles/themes/dark.css';

Alpine.data('lampadaApp', () => ({
    ...devotionalApp(),   // Lógica base que você já tinha
    ...calendarApp(),     // Lógica do calendario
    ...ambientMusic(),    // Lógica de áudio Web Audio API
    ...ttsHandler(),      // Lógica de Speech Synthesis
    ...aiChat(),          // Lógica de integração com API de IA
    ...whatsappHandler(), // Lógica de integração com WhatsApp
    ...landingApp(),      // Lógica do landing page
    ...gamificationApp(), // Lógica de gamificação
}));

Livewire.start();

// PWA Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => console.log('[SW] OK'))
            .catch(err => console.log('[SW] KO'));
    });
}
export const ambientMusic = () => ({
    isMusicPlaying: false,
    musicVolume: 0.30,
    _audioCtx: null,
    _musicNodes: [],
    _musicMaster: null,

    toggleAmbientMusic() {
        if (this.isMusicPlaying) {
            this.stopAmbientMusic();
        } else {
            this.startAmbientMusic();
        }
    },

    startAmbientMusic() {
        if (this._audioCtx && this.isMusicPlaying) { return; }

        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) {
            alert('Seu navegador não suporta áudio ambiente.');
            return;
        }

        const ctx = new AudioContext();
        this._audioCtx = ctx;
        this._musicNodes = [];

        // Master gain com fade-in suave (4s)
        const master = ctx.createGain();
        master.gain.setValueAtTime(0, ctx.currentTime);
        master.gain.linearRampToValueAtTime(this.musicVolume, ctx.currentTime + 4);
        master.connect(ctx.destination);
        this._musicMaster = master;

        // --- Reverb via feedback delay ---
        const delay = ctx.createDelay(2.0);
        const fbGain = ctx.createGain();
        const delayFilter = ctx.createBiquadFilter();
        delay.delayTime.value = 0.65;
        fbGain.gain.value = 0.42;
        delayFilter.type = 'lowpass';
        delayFilter.frequency.value = 700;
        delay.connect(delayFilter);
        delayFilter.connect(fbGain);
        fbGain.connect(delay); // loop de feedback

        const wetGain = ctx.createGain();
        wetGain.gain.value = 0.35;
        delayFilter.connect(wetGain);
        wetGain.connect(master);

        // Bus do PAD: seco vai pro master, molhado vai pro delay
        const padBus = ctx.createGain();
        padBus.gain.value = 1;
        padBus.connect(master);
        padBus.connect(delay);

        // --- PAD: Acorde Lá menor (A3, C4, E4, A4) ---
        const padNotes = [220, 261.63, 329.63, 440];
        padNotes.forEach((freq, i) => {
            // 2 osciladores por nota levemente desafinados = efeito chorus
            [-5, 5].forEach(detuneCents => {
                const osc = ctx.createOscillator();
                const oscGain = ctx.createGain();
                const filter = ctx.createBiquadFilter();

                osc.type = 'sine';
                osc.frequency.value = freq;
                osc.detune.value = detuneCents;

                filter.type = 'lowpass';
                filter.frequency.value = 1400;

                oscGain.gain.value = 0.045;

                // LFO individual para tremolo suave (ritmos diferentes por nota)
                const lfo = ctx.createOscillator();
                const lfoGain = ctx.createGain();
                lfo.type = 'sine';
                lfo.frequency.value = 0.06 + i * 0.02 + (detuneCents > 0 ? 0.01 : 0);
                lfoGain.gain.value = 0.012;
                lfo.connect(lfoGain);
                lfoGain.connect(oscGain.gain);
                lfo.start();

                osc.connect(filter);
                filter.connect(oscGain);
                oscGain.connect(padBus);
                osc.start();

                this._musicNodes.push(osc, lfo);
            });
        });

        // --- Binaural beats (requer fones de ouvido) ---
        // Esquerdo: 100 Hz | Direito: 108 Hz → batida de 8 Hz = onda alpha (foco relaxado)
        const merger = ctx.createChannelMerger(2);
        const binauralGain = ctx.createGain();
        binauralGain.gain.value = 0.07;
        merger.connect(binauralGain);
        binauralGain.connect(master);

        const leftOsc = ctx.createOscillator();
        leftOsc.type = 'sine';
        leftOsc.frequency.value = 100;

        const rightOsc = ctx.createOscillator();
        rightOsc.type = 'sine';
        rightOsc.frequency.value = 108;

        const leftGain = ctx.createGain();
        const rightGain = ctx.createGain();
        leftOsc.connect(leftGain);
        rightOsc.connect(rightGain);
        leftGain.connect(merger, 0, 0);  // canal esquerdo
        rightGain.connect(merger, 0, 1); // canal direito

        leftOsc.start();
        rightOsc.start();
        this._musicNodes.push(leftOsc, rightOsc);

        this.isMusicPlaying = true;
    },

    stopAmbientMusic() {
        if (!this._audioCtx) { return; }

        // Fade-out suave (2s) antes de destruir o contexto
        const gain = this._musicMaster;
        const ctx = this._audioCtx;
        gain.gain.cancelScheduledValues(ctx.currentTime);
        gain.gain.setValueAtTime(gain.gain.value, ctx.currentTime);
        gain.gain.linearRampToValueAtTime(0, ctx.currentTime + 2);

        setTimeout(() => {
            if (this._musicNodes) {
                this._musicNodes.forEach(node => { try { node.stop(); } catch (_) { } });
            }
            ctx.close();
            this._audioCtx = null;
            this._musicNodes = null;
            this._musicMaster = null;
            this.isMusicPlaying = false;
        }, 2200);
    },

    updateMusicRealtime() {
        if (!this.isSpeaking) return;
        if (this.ttsAutoMusic && !this.isMusicPlaying) {
            this.startAmbientMusic();
        } else if (!this.ttsAutoMusic && this.isMusicPlaying) {
            this.stopAmbientMusic();
        }
    },

});
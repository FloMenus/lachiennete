import { Controller } from '@hotwired/stimulus';

/*
 * Cosmetic "terminal" chrome: a fake live clock, a fake session id,
 * and a periodic glitch flicker on any [data-terminal-target="glitch"] element.
 * Purely decorative, no state that matters leaves this controller.
 */
export default class extends Controller {
    static targets = ['clock', 'session', 'glitch'];

    connect() {
        if (this.hasSessionTarget) {
            this.sessionTarget.textContent = `SESSION #${this.randomHex(8)}`;
        }

        this.tickClock();
        this.clockTimer = setInterval(() => this.tickClock(), 1000);
        this.scheduleGlitch();
    }

    disconnect() {
        clearInterval(this.clockTimer);
        clearTimeout(this.glitchTimer);
    }

    tickClock() {
        if (!this.hasClockTarget) {
            return;
        }

        const now = new Date();
        const pad = (n) => String(n).padStart(2, '0');
        this.clockTarget.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
    }

    scheduleGlitch() {
        const delay = 2200 + Math.random() * 4000;
        this.glitchTimer = setTimeout(() => {
            this.glitchTargets.forEach((el) => {
                el.classList.add('is-glitching');
                setTimeout(() => el.classList.remove('is-glitching'), 220);
            });
            this.scheduleGlitch();
        }, delay);
    }

    randomHex(length) {
        let out = '';
        for (let i = 0; i < length; i += 1) {
            out += Math.floor(Math.random() * 16).toString(16);
        }

        return out.toUpperCase();
    }
}

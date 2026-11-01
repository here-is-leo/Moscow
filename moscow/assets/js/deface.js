/**
 * MOSCOW Deface — client-side controller
 */
(function () {
    'use strict';

    const cfg = window.MOSCOW_DEFACE || {};
    const facts = Array.isArray(cfg.facts) ? cfg.facts : [];
    const totalSeconds = (cfg.hours || 50) * 3600;

    const el = {
        timer:  document.getElementById('timer-value'),
        fill:   document.getElementById('timer-fill'),
        fact:   document.getElementById('fact'),
        start:  document.getElementById('start-btn'),
    };

    if (!el.timer || !el.start) return;

    let remaining = totalSeconds;
    let running   = false;
    let tickId    = null;
    let factId    = null;
    let factIndex = 0;

    const shuffled = shuffle(facts.slice());

    function pad(n) { return String(n).padStart(2, '0'); }

    function format(s) {
        return `${pad(Math.floor(s / 3600))}:${pad(Math.floor((s % 3600) / 60))}:${pad(s % 60)}`;
    }

    function render() {
        el.timer.textContent = format(remaining);
        const pct = ((totalSeconds - remaining) / totalSeconds) * 100;
        if (el.fill) el.fill.style.width = `${pct.toFixed(2)}%`;
    }

    function nextFact() {
        if (!shuffled.length) return;
        el.fact.textContent = shuffled[factIndex % shuffled.length];
        factIndex++;
    }

    function tick() {
        if (remaining <= 0) {
            stop();
            el.timer.textContent = '00:00:00';
            el.timer.style.color = '#ff2d2d';
            el.fact.textContent = 'TIME IS UP. SYSTEM DESTROYED.';
            el.start.textContent = 'SEQUENCE COMPLETE';
            el.start.disabled = true;
            return;
        }
        remaining--;
        render();
    }

    function start() {
        if (running) return;
        running = true;
        el.start.disabled = true;
        el.start.innerHTML = '<span class="btn__icon">●</span> SEQUENCE RUNNING';

        nextFact();
        tickId = setInterval(tick, 1000);
        factId = setInterval(nextFact, 120000);
    }

    function stop() {
        running = false;
        clearInterval(tickId);
        clearInterval(factId);
        tickId = factId = null;
    }

    function shuffle(a) {
        for (let i = a.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [a[i], a[j]] = [a[j], a[i]];
        }
        return a;
    }

    el.start.addEventListener('click', start);
    render();
    initMatrix();
})();

/**
 * Matrix rain
 */
function initMatrix() {
    const canvas = document.getElementById('moscow-matrix');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const chars = '01アイウエオカキクケコサシスセソタチツテトМОSCOW'.split('');
    const fontSize = 14;
    let columns = 0;
    let drops = [];

    function resize() {
        canvas.width  = window.innerWidth;
        canvas.height = window.innerHeight;
        columns = Math.floor(canvas.width / fontSize);
        drops = Array(columns).fill(0).map(() => Math.random() * -100);
    }

    function draw() {
        ctx.fillStyle = 'rgba(5, 5, 7, 0.06)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        ctx.font = `${fontSize}px JetBrains Mono, monospace`;

        for (let i = 0; i < columns; i++) {
            const text = chars[Math.floor(Math.random() * chars.length)];
            const y = drops[i] * fontSize;
            ctx.fillStyle = Math.random() > 0.98 ? '#ff2d2d' : 'rgba(88, 166, 255, 0.55)';
            ctx.fillText(text, i * fontSize, y);

            if (y > canvas.height && Math.random() > 0.975) drops[i] = 0;
            drops[i]++;
        }
    }

    resize();
    window.addEventListener('resize', resize);
    setInterval(draw, 60);
}
/*
 * Motion helpers (no library).
 *   rsReducedMotion()  -> true when the device asks for less motion
 *   [data-rs-countup]  -> number counts up once when it becomes visible (visual only)
 *   rsCelebrate({ zIndex }) -> short confetti burst (zIndex default 1050)
 *   [data-rs-celebrate="key"] -> rsCelebrate() once per key per tab session
 *   rsPointsGained(points, anchor) -> floating "+N poin" (nothing when points is not above 0)
 * With reduced motion nothing here animates: the server text stays as it is.
 */
(function () {
    'use strict';

    window.rsReducedMotion = function () {
        return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    };

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    /* ---------- Count-up ---------- */
    function countUp(el) {
        var raw = el.getAttribute('data-rs-countup') || '';
        var target = parseFloat(raw);
        if (isNaN(target)) { return; }
        var dot = raw.indexOf('.');
        var decimals = dot === -1 ? 0 : raw.length - dot - 1;
        var original = el.textContent;
        var fmt = new Intl.NumberFormat('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
        var duration = 900;
        var start = null;

        el.setAttribute('aria-label', original.trim());

        function frame(now) {
            if (start === null) { start = now; }
            var t = Math.min(1, (now - start) / duration);
            var eased = 1 - Math.pow(1 - t, 3);
            if (t < 1) {
                el.textContent = fmt.format(target * eased);
                requestAnimationFrame(frame);
            } else {
                el.textContent = original; // exact server text
                el.removeAttribute('aria-label');
            }
        }
        el.textContent = fmt.format(0);
        requestAnimationFrame(frame);
    }

    function initCountUp() {
        if (window.rsReducedMotion() || !('IntersectionObserver' in window)) { return; }
        var items = document.querySelectorAll('[data-rs-countup]');
        if (!items.length) { return; }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) { return; }
                io.unobserve(entry.target);
                countUp(entry.target);
            });
        });
        Array.prototype.forEach.call(items, function (el) { io.observe(el); });
    }

    /* ---------- Confetti ---------- */
    var COLORS = ['#1266e4', '#1572FE', '#F5B301', '#ffd458', '#22c55e', '#ef4444'];

    window.rsCelebrate = function (opts) {
        if (window.rsReducedMotion()) { return; }
        var zIndex = (opts && opts.zIndex) || 1050;
        var canvas = document.createElement('canvas');
        canvas.setAttribute('aria-hidden', 'true');
        canvas.style.cssText = 'position:fixed;left:0;top:0;width:100%;height:100%;pointer-events:none;z-index:' + zIndex + ';';
        document.body.appendChild(canvas);
        var ctx = canvas.getContext('2d');
        var dpr = window.devicePixelRatio || 1;
        var w = 0;
        var h = 0;

        function resize() {
            w = window.innerWidth;
            h = window.innerHeight;
            canvas.width = Math.round(w * dpr);
            canvas.height = Math.round(h * dpr);
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }
        resize();
        window.addEventListener('resize', resize);

        var parts = [];
        for (var i = 0; i < 140; i++) {
            var fromLeft = i % 2 === 0;
            // aim up and toward the middle, with some spread
            var angle = (fromLeft ? -60 : -120) + (Math.random() * 40 - 20);
            var speed = 11 + Math.random() * 9;
            var rad = angle * Math.PI / 180;
            parts.push({
                x: fromLeft ? 0 : w,
                y: h,
                vx: Math.cos(rad) * speed,
                vy: Math.sin(rad) * speed,
                size: 6 + Math.random() * 6,
                rot: Math.random() * Math.PI * 2,
                vr: (Math.random() - 0.5) * 0.4,
                color: COLORS[i % COLORS.length]
            });
        }

        var DURATION = 2500;
        var begin = null;

        function done() {
            window.removeEventListener('resize', resize);
            if (canvas.parentNode) { canvas.parentNode.removeChild(canvas); }
        }

        function frame(now) {
            if (begin === null) { begin = now; }
            var elapsed = now - begin;
            if (elapsed >= DURATION) { done(); return; }
            var fade = elapsed > DURATION - 600 ? (DURATION - elapsed) / 600 : 1;
            ctx.clearRect(0, 0, w, h);
            ctx.globalAlpha = fade;
            parts.forEach(function (p) {
                p.vy += 0.32;
                p.vx *= 0.992;
                p.x += p.vx;
                p.y += p.vy;
                p.rot += p.vr;
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate(p.rot);
                ctx.fillStyle = p.color;
                ctx.fillRect(-p.size / 2, -p.size / 4, p.size, p.size / 2);
                ctx.restore();
            });
            requestAnimationFrame(frame);
        }
        requestAnimationFrame(frame);
    };

    /* ---------- Floating "+N poin" ---------- */
    window.rsPointsGained = function (points, anchor) {
        points = Number(points);
        if (!(points > 0)) { return; }
        var pop = document.createElement('div');
        pop.className = 'rs-points-pop';
        pop.setAttribute('role', 'status');
        var icon = document.createElement('i');
        icon.className = 'fa fa-star';
        icon.setAttribute('aria-hidden', 'true');
        var text = document.createElement('span');
        text.textContent = '+' + new Intl.NumberFormat('id-ID').format(points) + ' poin';
        pop.appendChild(icon);
        pop.appendChild(text);

        var x = window.innerWidth / 2;
        var y = window.innerHeight / 3;
        if (anchor && anchor.getBoundingClientRect) {
            var r = anchor.getBoundingClientRect();
            if (r.width > 0 || r.height > 0) {
                x = r.left + r.width / 2;
                y = r.top - 8;
            }
        }
        x = Math.max(70, Math.min(window.innerWidth - 70, x));
        pop.style.left = x + 'px';
        pop.style.visibility = 'hidden';
        document.body.appendChild(pop);
        // Sit above the anchor (bottom edge at y), not over it
        y = Math.max(60, y - pop.offsetHeight);
        pop.style.top = y + 'px';
        pop.style.visibility = '';

        var still = window.rsReducedMotion();
        pop.classList.add(still ? 'is-still' : 'is-moving');
        setTimeout(function () {
            if (pop.parentNode) { pop.parentNode.removeChild(pop); }
        }, still ? 2000 : 1600);
    };

    function initCelebrate() {
        var el = document.querySelector('[data-rs-celebrate]');
        if (!el) { return; }
        var key = 'rs-celebrated:' + el.getAttribute('data-rs-celebrate');
        try {
            if (sessionStorage.getItem(key)) { return; }
            sessionStorage.setItem(key, '1');
        } catch (e) {}
        window.rsCelebrate();
    }

    ready(function () {
        initCountUp();
        initCelebrate();
    });
}());

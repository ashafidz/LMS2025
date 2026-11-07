{{-- Intro "logo tersusun": home page only, once per tab session, skipped for reduced motion. Styles: ruang-studi.css (/* Intro */). --}}
<div class="rs-intro" id="rs-intro" aria-hidden="true">
    <div class="rs-intro__lockup">
        <span class="rs-intro__mark">
            <span class="rs-intro__piece"></span><span class="rs-intro__piece"></span><span class="rs-intro__piece"></span><span class="rs-intro__piece"></span>
            <span class="rs-intro__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4h6a4 4 0 0 1 4 4v13a3 3 0 0 0-3-3H2z"/><path d="M22 4h-6a4 4 0 0 0-4 4v13a3 3 0 0 1 3-3h7z"/></svg>
            </span>
            <svg class="rs-intro__star" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.9 6.6 7.1.6-5.4 4.7 1.6 7-6.2-3.7-6.2 3.7 1.6-7L2 9.2l7.1-.6z"/></svg>
        </span>
        <p class="rs-intro__name">{{ ($siteSettings->site_name ?? null) ?: config('app.name') }}</p>
        <span class="rs-intro__tag">Selamat datang, ayo mulai belajar</span>
    </div>
    <button type="button" class="rs-intro__skip" tabindex="-1">Lewati</button>
</div>
<script>
(function () {
    var intro = document.getElementById('rs-intro');
    if (!intro) { return; }
    var KEY = 'rs-intro-seen';
    var seen = false;
    try { seen = !!sessionStorage.getItem(KEY); } catch (e) {}
    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (seen || reduced) {
        intro.remove();
        document.body.classList.remove('rs-intro-on');
        return;
    }

    var name = intro.querySelector('.rs-intro__name');
    var timers = [];

    // Split the name into letters, each with its own fixed start offset
    var text = name.textContent;
    name.setAttribute('aria-label', text);
    name.textContent = '';
    Array.prototype.forEach.call(text, function (ch, i) {
        var s = document.createElement('span');
        s.className = 'rs-intro__ch';
        s.textContent = ch;
        s.style.setProperty('--i', i);
        s.style.setProperty('--dx', (((i * 37) % 9) - 4) * 22 + 'px');
        s.style.setProperty('--dy', (((i * 53) % 7) - 3) * 26 + 'px');
        s.style.setProperty('--dr', (((i * 29) % 11) - 5) * 12 + 'deg');
        name.appendChild(s);
    });

    // Fly the name onto the real navbar logo (same font, so only position and scale change)
    function leave() {
        var logo = document.querySelector('.rs-logo');
        if (logo && logo.offsetParent !== null) {
            var n = name.getBoundingClientRect();
            var l = logo.getBoundingClientRect();
            var fs = parseFloat(getComputedStyle(logo).fontSize) / parseFloat(getComputedStyle(name).fontSize);
            intro.style.setProperty('--fs', fs);
            intro.style.setProperty('--fx', (l.left - n.left) + 'px');
            intro.style.setProperty('--fy', (l.top + l.height / 2 - n.top - n.height * fs / 2) + 'px');
            // Close only once the name has landed on the logo, so the hand-off never jumps
            name.addEventListener('transitionend', function (e) {
                if (e.target === name && e.propertyName === 'transform') { finish(); }
            });
        } else {
            timers.push(setTimeout(finish, 450));
        }
        intro.classList.add('is-leaving');
    }

    function finish() {
        timers.forEach(clearTimeout);
        timers = [];
        intro.remove();
        document.body.classList.remove('rs-intro-on');
        document.removeEventListener('keydown', finish);
    }

    document.body.classList.add('rs-intro-on');
    try { sessionStorage.setItem(KEY, '1'); } catch (e) {}
    timers.push(setTimeout(leave, 1050));
    timers.push(setTimeout(finish, 2000)); // safety net if transitionend never fires
    intro.addEventListener('click', finish);
    document.addEventListener('keydown', finish);
})();
</script>

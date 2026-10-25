/**
 * Ukuran huruf pratinjau sertifikat untuk browser tanpa satuan cqw (Safari < 16, Chrome < 105).
 * Browser modern tidak menjalankan apa pun di sini. Setiap teks punya data-font-fraction (ukuran / lebar halaman);
 * ukuran huruf = lebar kanvas sebenarnya x fraksi, dihitung ulang saat lebar kanvas berubah.
 */
(function () {
    function supportsContainerUnits() {
        return !!(window.CSS && CSS.supports && CSS.supports('width', '1cqw'));
    }

    function fit(canvas) {
        var width = canvas.clientWidth;
        if (!width) {
            return;
        }
        var items = canvas.querySelectorAll('[data-font-fraction]');
        for (var i = 0; i < items.length; i++) {
            items[i].style.fontSize = (width * parseFloat(items[i].getAttribute('data-font-fraction'))) + 'px';
        }
    }

    window.fitCertificateCanvas = function (root) {
        if (supportsContainerUnits()) {
            return;
        }
        var canvases = (root || document).querySelectorAll('.certificate-canvas');
        for (var i = 0; i < canvases.length; i++) {
            (function (canvas) {
                fit(canvas);
                if (window.ResizeObserver) {
                    new ResizeObserver(function () { fit(canvas); }).observe(canvas);
                } else {
                    window.addEventListener('resize', function () { fit(canvas); });
                }
            })(canvases[i]);
        }
    };
})();

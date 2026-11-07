@php
    $messages = [];
    foreach ([['rs_toast', 'success'], ['success', 'success'], ['warning', 'warning'], ['info', 'info']] as [$key, $icon]) {
        $text = session($key);
        if (is_string($text) && trim($text) !== '') {
            $messages[] = ['text' => $text, 'icon' => $icon];
        }
    }
@endphp
@if ($messages)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var rsFlashQueue = {{ \Illuminate\Support\Js::from($messages) }};
            if (typeof rsToast !== 'function') { return; }
            // Wait for a modal that opened first (badge celebration), otherwise the toast would replace it.
            (window.rsModalGate || Promise.resolve()).then(function next() {
                var m = rsFlashQueue.shift();
                if (!m) { return; }
                rsToast(m.text, m.icon).then(next);
            });
        });
    </script>
@endif

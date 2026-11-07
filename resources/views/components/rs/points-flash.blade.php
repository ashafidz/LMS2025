@php($earned = session('rs_points'))
@if (is_numeric($earned) && (int) $earned > 0)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof rsPointsGained === 'function') { rsPointsGained({{ (int) $earned }}); }
        });
    </script>
@endif

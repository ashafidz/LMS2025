{{-- Pratinjau sertifikat dengan posisi dalam persen halaman. Ukuran huruf memakai cqw (persen lebar kanvas).
     Untuk browser lama: tinggi dari padding-top (tanpa aspect-ratio). Ukuran huruf di browser tanpa cqw dihitung
     dari lebar kanvas sebenarnya oleh public/js/certificate-canvas-fallback.js (data-font-fraction = ukuran / lebar).
     Nilai calc() sebelum cqw hanya perkiraan kasar untuk browser lama dengan JavaScript mati. --}}
<style>
    {!! $fontFaceCss !!}
</style>
<div class="certificate-canvas" style="position: relative; width: 100%; height: 0; padding-top: {{ number_format($pageH / $pageW * 100, 4, '.', '') }}%; container-type: inline-size; overflow: hidden; border: 1px solid #ddd; background: #fff;">
    <img src="{{ $backgroundUrl }}" alt="" style="position: absolute; left: 0; top: 0; width: 100%; height: 100%;">
    @foreach ($elements as $e)
        <div data-font-fraction="{{ $e['font_fraction'] }}" style="position: absolute; left: {{ $e['left_pct'] }}%; top: {{ $e['top_pct'] }}%; width: {{ $e['width_pct'] }}%; transform: translateX({{ $e['translate'] }}); text-align: {{ $e['align'] }}; font-family: '{{ $e['family'] }}', sans-serif; font-size: calc(min(800px, 100vw - 64px) * {{ $e['font_fraction'] }}); font-size: {{ $e['font_cqw'] }}cqw; line-height: 1.2; color: {{ $e['color'] }}; font-weight: {{ $e['bold'] ? 'bold' : 'normal' }}; font-style: {{ $e['italic'] ? 'italic' : 'normal' }};">{{ $e['text'] }}</div>
    @endforeach
</div>

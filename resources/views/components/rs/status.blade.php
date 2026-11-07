@props(['title', 'icon' => 'bi-info-circle', 'tone' => 'info', 'eyebrow' => null, 'illustration' => null])
<div {{ $attributes->merge(['class' => 'rs-status rs-status--'.$tone]) }}>
    @if ($illustration)
        <x-rs.illustration :name="$illustration" size="md" class="rs-status__illu" />
    @else
        <i class="bi {{ $icon }} rs-status__icon" aria-hidden="true"></i>
    @endif
    @if ($eyebrow)
        <p class="rs-eyebrow">{{ $eyebrow }}</p>
    @endif
    <h1 class="rs-h2">{{ $title }}</h1>
    {{ $slot }}
</div>

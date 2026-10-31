@props(['title', 'icon' => 'bi-info-circle', 'tone' => 'info', 'eyebrow' => null])
<div {{ $attributes->merge(['class' => 'rs-status rs-status--'.$tone]) }}>
    <i class="bi {{ $icon }} rs-status__icon" aria-hidden="true"></i>
    @if ($eyebrow)
        <p class="rs-eyebrow">{{ $eyebrow }}</p>
    @endif
    <h1 class="rs-h2">{{ $title }}</h1>
    {{ $slot }}
</div>

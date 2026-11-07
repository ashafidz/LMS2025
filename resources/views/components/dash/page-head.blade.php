@props(['title' => null, 'subtitle' => null, 'context' => null, 'crumbs' => [], 'actions' => null, 'titleId' => null])
<header {{ $attributes->merge(['class' => 'rs-phead']) }}>
    @if (! empty($crumbs))
        <nav class="rs-phead__crumbs" aria-label="Jejak halaman">
            <ol>
                @foreach ($crumbs as $crumb)
                    @php [$label, $url] = $crumb + [1 => null]; $isLast = $loop->last; @endphp
                    <li>
                        @if ($label === '__home' && $url)
                            <a href="{{ $url }}" aria-label="Beranda"><i class="bi bi-house-door" aria-hidden="true"></i></a>
                        @elseif ($label === '__home')
                            <span aria-label="Beranda"><i class="bi bi-house-door" aria-hidden="true"></i></span>
                        @elseif ($url)
                            <a href="{{ $url }}">{{ $label }}</a>
                        @elseif ($isLast)
                            <span aria-current="page">{{ $label }}</span>
                        @else
                            <span>{{ $label }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif
    <div class="rs-phead__row">
        <div class="rs-phead__text">
            @if ($context && trim((string) $context) !== '')
                <p class="rs-phead__context">{{ $context }}</p>
            @endif
            <h1 @if ($titleId) id="{{ $titleId }}" @endif class="rs-phead__title">{{ $title }}</h1>
            @if ($subtitle && trim((string) $subtitle) !== '')
                <p class="rs-phead__sub">{{ $subtitle }}</p>
            @endif
        </div>
        @if ($actions && trim((string) $actions) !== '')
            <div class="rs-phead__actions">{{ $actions }}</div>
        @endif
    </div>
</header>

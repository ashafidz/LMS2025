@props([
    'placeholder' => 'Cari...',
    'label' => 'Cari data',
    'tab' => null,
])
{{--
    Search box for dashboard lists (GET ?search=). Keeps the current sort, direction, page size, tab, and any other
    filters in hidden inputs, and drops the page number so a new search starts on page 1. Reset removes only the search.
--}}
@php
    $hasSearch = request()->filled('search');
    $isPageKey = fn ($k) => $k !== 'per_page' && ($k === 'page' || str_starts_with((string) $k, 'page_') || str_ends_with((string) $k, '_page'));
    $keep = collect(request()->query())
        ->reject(fn ($v, $k) => $k === 'search' || $isPageKey($k) || ($tab !== null && $k === 'tab'))
        ->filter(fn ($v) => is_scalar($v) && $v !== '');
    $resetQuery = collect(request()->query())
        ->reject(fn ($v, $k) => $k === 'search' || $isPageKey($k))
        ->all();
    $resetUrl = url()->current().($resetQuery ? '?'.http_build_query($resetQuery) : '');
@endphp
<form action="{{ url()->current() }}" method="GET" {{ $attributes->merge(['class' => 'rs-list-toolbar mb-3']) }} role="search">
    @foreach ($keep as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
    @if ($tab !== null)
        <input type="hidden" name="tab" value="{{ $tab }}" data-rs-current-tab>
    @endif
    <div class="rs-search-row">
        <div class="input-group">
            <input type="text" name="search" class="form-control" aria-label="{{ $label }}"
                placeholder="{{ $placeholder }}" value="{{ request('search') }}" maxlength="100">
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </div>
        @if ($hasSearch)
            <a href="{{ $resetUrl }}" class="btn btn-secondary">Reset</a>
        @endif
    </div>
    @if ($hasSearch)
        <small class="text-muted d-block mt-2">Menampilkan hasil yang cocok dengan "{{ request('search') }}".</small>
    @endif
    {{ $slot }}
</form>

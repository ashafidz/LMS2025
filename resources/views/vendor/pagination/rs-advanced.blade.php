{{--
    Advanced dashboard pagination: "Menampilkan X-Y dari Z data", page size, first/previous/numbered/next/last,
    and "Ke halaman". Use it through <x-dash.pagination :items="$items" />.
    Every form keeps the other query values (search, sort, tab, filters), so changing page never loses them.
--}}
@php
    $pageName = $paginator->getPageName();
    $total = $paginator->total();
    $sizeOptions = $perPageOptions ?? \App\Support\ListQuery::PER_PAGE_OPTIONS;
    $showSize = ($showPerPage ?? true) && $total > min($sizeOptions);
    // Query values to keep in the GET forms (scalars only), minus the ones the form itself sets. Read from the
    // paginator's own URL so values it appends (for example the tab of this list) are kept too.
    parse_str((string) parse_url($paginator->url($paginator->currentPage()), PHP_URL_QUERY), $pagerQuery);
    $keepWithSize = collect($pagerQuery)
        ->except([$pageName])
        ->filter(fn ($v) => is_scalar($v) && $v !== '');
    $keep = $keepWithSize->except(['per_page']);
@endphp
<div class="rs-pager">
    <div class="rs-pager__info">
        @if ($total > 0 && $paginator->count() === 0)
            <span>Halaman {{ $paginator->currentPage() }} kosong. <a href="{{ $paginator->url($paginator->lastPage()) }}">Buka halaman terakhir ({{ $paginator->lastPage() }})</a></span>
        @elseif ($total > 0)
            <span>Menampilkan <strong>{{ $paginator->firstItem() }}-{{ $paginator->lastItem() }}</strong> dari <strong>{{ $total }}</strong> data</span>
        @else
            <span>Tidak ada data</span>
        @endif

        @if ($showSize)
            <form method="GET" action="{{ $paginator->path() }}" class="rs-pager__size">
                @foreach ($keep as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach
                <label for="rs-per-page-{{ $pageName }}">Tampilkan</label>
                <select id="rs-per-page-{{ $pageName }}" name="per_page" class="form-control no-select2" data-rs-autosubmit>
                    @foreach ($sizeOptions as $size)
                        <option value="{{ $size }}" @selected($paginator->perPage() === $size)>{{ $size }}</option>
                    @endforeach
                </select>
                <span>per halaman</span>
                <noscript><button type="submit" class="btn btn-secondary btn-sm">Terapkan</button></noscript>
            </form>
        @endif
    </div>

    @if ($paginator->hasPages())
        <nav class="rs-pager__nav" aria-label="Navigasi halaman">
            <ul class="rs-pager__list">
                {{-- First and previous --}}
                @if ($paginator->onFirstPage())
                    <li><span class="rs-pager__btn is-disabled" aria-disabled="true"><i class="bi bi-chevron-double-left" aria-hidden="true"></i><span class="rs-pager__label">Pertama</span></span></li>
                    <li><span class="rs-pager__btn is-disabled" aria-disabled="true"><i class="bi bi-chevron-left" aria-hidden="true"></i><span class="rs-pager__label">Sebelumnya</span></span></li>
                @else
                    <li><a class="rs-pager__btn" href="{{ $paginator->url(1) }}" aria-label="Halaman pertama"><i class="bi bi-chevron-double-left" aria-hidden="true"></i><span class="rs-pager__label">Pertama</span></a></li>
                    <li><a class="rs-pager__btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"><i class="bi bi-chevron-left" aria-hidden="true"></i><span class="rs-pager__label">Sebelumnya</span></a></li>
                @endif

                {{-- Numbered pages with ellipsis (hidden on phones; replaced by "Hal X dari Y") --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="rs-pager__num"><span class="rs-pager__gap" aria-hidden="true">...</span></li>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="rs-pager__num"><span class="rs-pager__btn is-current" aria-current="page">{{ $page }}</span></li>
                            @else
                                <li class="rs-pager__num"><a class="rs-pager__btn" href="{{ $url }}" aria-label="Halaman {{ $page }}">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach
                <li class="rs-pager__compact"><span>Hal {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span></li>

                {{-- Next and last --}}
                @if ($paginator->hasMorePages())
                    <li><a class="rs-pager__btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"><span class="rs-pager__label">Berikutnya</span><i class="bi bi-chevron-right" aria-hidden="true"></i></a></li>
                    <li><a class="rs-pager__btn" href="{{ $paginator->url($paginator->lastPage()) }}" aria-label="Halaman terakhir"><span class="rs-pager__label">Terakhir</span><i class="bi bi-chevron-double-right" aria-hidden="true"></i></a></li>
                @else
                    <li><span class="rs-pager__btn is-disabled" aria-disabled="true"><span class="rs-pager__label">Berikutnya</span><i class="bi bi-chevron-right" aria-hidden="true"></i></span></li>
                    <li><span class="rs-pager__btn is-disabled" aria-disabled="true"><span class="rs-pager__label">Terakhir</span><i class="bi bi-chevron-double-right" aria-hidden="true"></i></span></li>
                @endif
            </ul>

            <form method="GET" action="{{ $paginator->path() }}" class="rs-pager__jump">
                @foreach ($keepWithSize as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach
                <label for="rs-jump-{{ $pageName }}">Ke halaman</label>
                <input id="rs-jump-{{ $pageName }}" type="number" name="{{ $pageName }}" min="1" max="{{ $paginator->lastPage() }}"
                    value="{{ $paginator->currentPage() }}" class="form-control" inputmode="numeric">
                <button type="submit" class="btn btn-secondary btn-sm">Buka</button>
            </form>
        </nav>
    @endif
</div>

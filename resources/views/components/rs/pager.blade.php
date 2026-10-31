@props(['paginator'])
@if ($paginator->hasPages())
    <nav class="rs-pager" aria-label="Navigasi halaman">
        @if ($paginator->onFirstPage())
            <span class="rs-btn rs-btn--outline is-disabled" aria-disabled="true">Sebelumnya</span>
        @else
            <a class="rs-btn rs-btn--outline" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
        @endif
        @if ($paginator->hasMorePages())
            <a class="rs-btn rs-btn--outline" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
        @else
            <span class="rs-btn rs-btn--outline is-disabled" aria-disabled="true">Berikutnya</span>
        @endif
    </nav>
@endif

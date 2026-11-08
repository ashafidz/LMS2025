@props(['items', 'perPageOptions' => null, 'showPerPage' => true])
{{-- Advanced pagination for a LengthAwarePaginator. Renders nothing for a plain collection. --}}
@if ($items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
    {{ $items->onEachSide(2)->links('vendor.pagination.rs-advanced', array_filter([
        'perPageOptions' => $perPageOptions,
        'showPerPage' => $showPerPage,
    ], fn ($v) => $v !== null)) }}
@endif

{{--
    Sortable table header. Params: $key (sort key), $label, $class (optional th class), $first (optional, direction on first click),
    $tab (optional, tab to reopen after the reload).
    Needs $tableSort and $tableDir from the controller. Keeps ?search= and other query values.
--}}
@php
    $isActive = ($tableSort ?? null) === $key;
    $nextDir = $isActive ? (($tableDir ?? 'asc') === 'asc' ? 'desc' : 'asc') : ($first ?? 'asc');
    $ariaSort = $isActive ? (($tableDir ?? 'asc') === 'asc' ? 'ascending' : 'descending') : 'none';
@endphp
<th class="{{ $class ?? '' }}" aria-sort="{{ $ariaSort }}">
    <a href="{{ request()->fullUrlWithQuery(array_filter(['sort' => $key, 'dir' => $nextDir, 'tab' => $tab ?? null])) }}" class="rs-sort-link{{ $isActive ? ' is-active' : '' }}" title="Urutkan berdasarkan {{ $label }}">{{ $label }}<i class="bi {{ $isActive ? (($tableDir ?? 'asc') === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i></a>
</th>

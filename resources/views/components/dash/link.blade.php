@props(['href', 'active' => [], 'icon' => null])
@php
    $isActive = request()->routeIs(...(array) $active);
@endphp
<li><a href="{{ $href }}" aria-current="{{ $isActive ? 'page' : 'false' }}">@if ($icon)<span class="rs-dash-ic" aria-hidden="true"><i class="fa fa-{{ $icon }}"></i></span>@endif{{ $slot }}</a></li>

@props(['href', 'active' => []])
@php
    $isActive = request()->routeIs(...(array) $active);
@endphp
<li><a href="{{ $href }}" aria-current="{{ $isActive ? 'page' : 'false' }}">{{ $slot }}</a></li>

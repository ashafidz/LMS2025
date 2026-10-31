@props(['value' => 0])
@php $rounded = (int) round($value); @endphp
<span class="rs-stars" role="img" aria-label="Nilai {{ $rounded }} dari 5">
    @for ($i = 1; $i <= 5; $i++)
        <i class="bi {{ $i <= $rounded ? 'bi-star-fill' : 'bi-star' }}" aria-hidden="true"></i>
    @endfor
</span>

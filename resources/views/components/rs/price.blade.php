@props(['course'])
@if ($course->payment_type === 'diamonds')
    <span {{ $attributes->merge(['class' => 'rs-price rs-price--diamond']) }}>
        <i class="bi bi-gem" aria-hidden="true"></i>{{ number_format($course->diamond_price, 0, ',', '.') }} Diamond
    </span>
@elseif ($course->price > 0)
    <span {{ $attributes->merge(['class' => 'rs-price']) }}>Rp{{ number_format($course->price, 0, ',', '.') }}</span>
@else
    <span {{ $attributes->merge(['class' => 'rs-price']) }}>Gratis</span>
@endif

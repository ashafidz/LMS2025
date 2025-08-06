{{-- resources/views/student/transactions/_invoice.blade.php --}}

@php
    // Logika untuk menentukan judul, ikon, dan warna berdasarkan status pesanan
    $statusInfo = [
        'paid' => ['title' => 'Pembelian Berhasil', 'icon' => 'fa-check-circle', 'color' => 'success'],
        'pending' => ['title' => 'Pembayaran Tertunda', 'icon' => 'fa-clock-o', 'color' => 'warning'],
        'failed' => ['title' => 'Transaksi Gagal', 'icon' => 'fa-times-circle', 'color' => 'danger'],
        'cancelled' => ['title' => 'Transaksi Dibatalkan', 'icon' => 'fa-info-circle', 'color' => 'default'],
    ];
    $currentStatus = $statusInfo[$order->status] ?? ['title' => 'Detail Transaksi', 'icon' => 'fa-receipt', 'color' => 'primary'];
@endphp

<div class="invoice-container p-md-3">
    {{-- Bagian Header --}}
    <div class="text-center mb-4">
        <i class="fa {{ $currentStatus['icon'] }} text-{{ $currentStatus['color'] }}" style="font-size: 4rem;"></i>
        <h2 class="font-weight-bold mt-3">{{ $currentStatus['title'] }}</h2>
        <p class="text-muted">
            Terima kasih! Berikut detail transaksi Anda.
        </p>
    </div>

    {{-- Detail Transaksi Utama --}}
    <div class="bg-light rounded p-3 mb-4" style="background-color: #f8f9fa !important;">
        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">No. Transaksi:</span>
            <span class="font-weight-bold">{{ $order->order_code }}</span>
        </div>
        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Tanggal:</span>
            <span>{{ $order->updated_at->format('d F Y, H:i') }} WIB</span>
        </div>
        <div class="d-flex justify-content-between">
            <span class="text-muted">Status Pembayaran:</span>
            <span class="font-weight-bold text-{{ $currentStatus['color'] }}">{{ ucfirst($order->status) }}</span>
        </div>
    </div>

    {{-- Rincian Pembayaran --}}
    <h5 class="font-weight-bold mb-3">Detail Pembayaran</h5>
    
    @foreach($order->items as $item)
    <div class="d-flex justify-content-between">
        <span>1 &times; {{ $item->course->title }}</span>
        <span>Rp{{ number_format($item->price_at_purchase, 0, ',', '.') }}</span>
    </div>
    @endforeach
    
    <hr>

    <div class="d-flex justify-content-between">
        <span class="text-muted">Subtotal</span>
        <span>Rp{{ number_format($order->total_amount, 0, ',', '.') }}</span>
    </div>

    @if($order->discount_amount > 0)
    <div class="d-flex justify-content-between">
        <span class="text-muted">Diskon Kupon</span>
        <span>- Rp{{ number_format($order->discount_amount, 0, ',', '.') }}</span>
    </div>
    @endif

    @if($order->vat_amount > 0)
    <div class="d-flex justify-content-between">
        <span class="text-muted">PPN ({{ rtrim(rtrim($order->vat_percentage_at_purchase, '0'), '.') }}%)</span>
        <span>+ Rp{{ number_format($order->vat_amount, 0, ',', '.') }}</span>
    </div>
    @endif

    @if($order->transaction_fee_amount > 0)
    <div class="d-flex justify-content-between">
        <span class="text-muted">Biaya Layanan</span>
        <span>+ Rp{{ number_format($order->transaction_fee_amount, 0, ',', '.') }}</span>
    </div>
    @endif

    <hr>

    <div class="d-flex justify-content-between font-weight-bold" style="font-size: 1.2em;">
        <span>Total Dibayar:</span>
        <span class="text-primary">Rp{{ number_format($order->final_amount, 0, ',', '.') }}</span>
    </div>
</div>
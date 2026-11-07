@props(['name', 'size' => 'md'])
@php
    $sizes = [
        'rs-01-hero-belajar' => [687, 771],
        'rs-02-tentang-tim' => [800, 800],
        'rs-03-masuk' => [560, 560],
        'rs-04-daftar' => [370, 560],
        'rs-05-lupa-sandi' => [560, 560],
        'rs-06-verifikasi-email' => [428, 560],
        'rs-07-cari-kursus' => [385, 560],
        'rs-08-semua-selesai' => [560, 560],
        'rs-09-sertifikat-kosong' => [463, 560],
        'rs-10-transaksi-kosong' => [420, 402],
        'rs-11-keranjang-kosong' => [560, 539],
        'rs-12-instruktur-mulai' => [560, 428],
        'rs-13-kuis-lulus' => [402, 560],
        'rs-14-kuis-coba-lagi' => [511, 560],
        'rs-15-sertifikat-terbit' => [430, 560],
        'rs-16-bayar-berhasil' => [560, 560],
        'rs-17-bayar-menunggu' => [451, 560],
        'rs-18-bayar-gagal' => [560, 559],
        'rs-19-status-akun' => [441, 560],
        'rs-20-tersesat' => [800, 670],
    ];
    $dims = $sizes[$name] ?? null;
@endphp
@if ($dims)
<img src="{{ asset('images/illustrations/'.$name.'.webp') }}" alt="" width="{{ $dims[0] }}" height="{{ $dims[1] }}"
     {{ $attributes->merge(['class' => 'rs-illu rs-illu--'.$size, 'loading' => 'lazy', 'decoding' => 'async']) }}>
@endif

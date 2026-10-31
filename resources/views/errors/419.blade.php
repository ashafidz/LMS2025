@extends('layouts.plain-layout')

@section('title', 'Halaman sudah kedaluwarsa')

@section('content')
<x-rs.status icon="bi-hourglass-bottom" eyebrow="Kode 419" title="Halaman sudah kedaluwarsa">
    <p>Halaman ini terlalu lama dibuka sehingga perlu dimuat ulang. Kembali ke halaman sebelumnya, muat ulang, lalu coba lagi.</p>
    <div class="rs-status__actions">
        <a href="{{ url()->previous() }}" class="rs-btn rs-btn--block">Kembali ke halaman sebelumnya</a>
        <a href="{{ route('login') }}" class="rs-btn rs-btn--outline rs-btn--block">Masuk lagi</a>
    </div>
</x-rs.status>
@endsection

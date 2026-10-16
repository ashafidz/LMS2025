@extends('layouts.plain-layout')

@section('title', 'Sistem terlalu lama merespons')

@section('content')
<x-rs.status icon="bi-clock-history" eyebrow="Kode 504" title="Sistem terlalu lama merespons">
    <p>Permintaanmu butuh waktu terlalu lama. Coba lagi beberapa saat lagi.</p>
    <div class="rs-status__actions">
        <a href="{{ url()->current() }}" class="rs-btn rs-btn--block">Muat ulang</a>
        <a href="{{ url('/') }}" class="rs-btn rs-btn--outline rs-btn--block">Ke beranda</a>
    </div>
</x-rs.status>
@endsection

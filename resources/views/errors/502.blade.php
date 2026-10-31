@extends('layouts.plain-layout')

@section('title', 'Layanan sedang terganggu')

@section('content')
<x-rs.status icon="bi-cloud-slash" eyebrow="Kode 502" title="Layanan sedang terganggu">
    <p>Sistem kami sedang tidak bisa dihubungi. Coba lagi beberapa saat lagi.</p>
    <div class="rs-status__actions">
        <a href="{{ url('/') }}" class="rs-btn rs-btn--block">Ke beranda</a>
    </div>
</x-rs.status>
@endsection

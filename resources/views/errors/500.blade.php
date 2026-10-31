@extends('layouts.plain-layout')

@section('title', 'Terjadi gangguan')

@section('content')
<x-rs.status icon="bi-tools" eyebrow="Kode 500" title="Terjadi gangguan">
    <p>Ada masalah di sistem kami. Coba lagi beberapa saat lagi. Jika masih terjadi, hubungi pengelola situs.</p>
    <div class="rs-status__actions">
        <a href="{{ url('/') }}" class="rs-btn rs-btn--block">Ke beranda</a>
    </div>
</x-rs.status>
@endsection

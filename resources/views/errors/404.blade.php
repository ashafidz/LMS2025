@extends('layouts.plain-layout')

@section('title', 'Halaman tidak ditemukan')

@section('content')
<x-rs.status icon="bi-signpost-split" eyebrow="Kode 404" title="Halaman tidak ditemukan">
    <p>Halaman yang kamu cari tidak ada atau sudah dipindahkan.</p>
    <div class="rs-status__actions">
        <a href="{{ url('/') }}" class="rs-btn rs-btn--block">Ke beranda</a>
        <a href="{{ route('courses') }}" class="rs-btn rs-btn--outline rs-btn--block">Lihat katalog kursus</a>
    </div>
</x-rs.status>
@endsection

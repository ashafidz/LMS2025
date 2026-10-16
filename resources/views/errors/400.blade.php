@extends('layouts.plain-layout')

@section('title', 'Permintaan tidak dapat diproses')

@section('content')
<x-rs.status icon="bi-exclamation-circle" eyebrow="Kode 400" title="Permintaan tidak dapat diproses">
    <p>Ada data yang tidak sesuai saat membuka halaman ini. Kembali ke halaman sebelumnya lalu coba lagi.</p>
    <div class="rs-status__actions">
        <a href="{{ url()->previous() }}" class="rs-btn rs-btn--block">Kembali ke halaman sebelumnya</a>
        <a href="{{ url('/') }}" class="rs-btn rs-btn--outline rs-btn--block">Ke beranda</a>
    </div>
</x-rs.status>
@endsection

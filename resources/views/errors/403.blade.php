@extends('layouts.plain-layout')

@section('title', 'Akses ditolak')

@section('content')
<x-rs.status icon="bi-shield-lock" eyebrow="Kode 403" title="Akses ditolak">
    <p>Akunmu tidak punya izin untuk membuka halaman ini. Jika menurutmu ini keliru, hubungi admin sekolah atau pengelola situs.</p>
    <div class="rs-status__actions">
        <a href="{{ url('/') }}" class="rs-btn rs-btn--block">Ke beranda</a>
    </div>
</x-rs.status>
@endsection

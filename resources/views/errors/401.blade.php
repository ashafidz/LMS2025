@extends('layouts.plain-layout')

@section('title', 'Kamu perlu masuk dulu')

@section('content')
<x-rs.status icon="bi-lock" eyebrow="Kode 401" title="Kamu perlu masuk dulu">
    <p>Halaman ini hanya bisa dibuka setelah kamu masuk ke akunmu.</p>
    <div class="rs-status__actions">
        <a href="{{ route('login') }}" class="rs-btn rs-btn--block">Masuk</a>
        <a href="{{ url('/') }}" class="rs-btn rs-btn--outline rs-btn--block">Ke beranda</a>
    </div>
</x-rs.status>
@endsection

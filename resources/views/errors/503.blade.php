@extends('layouts.plain-layout')

@section('title', 'Sedang dalam perawatan')

@section('content')
<x-rs.status icon="bi-cone-striped" eyebrow="Kode 503" title="Sedang dalam perawatan">
    <p>Situs sedang dirawat atau sedang sangat sibuk. Coba lagi beberapa saat lagi.</p>
    <div class="rs-status__actions">
        <a href="{{ url('/') }}" class="rs-btn rs-btn--block">Ke beranda</a>
    </div>
</x-rs.status>
@endsection

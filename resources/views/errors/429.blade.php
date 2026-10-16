@extends('layouts.plain-layout')

@section('title', 'Terlalu banyak percobaan')

@section('content')
<x-rs.status icon="bi-speedometer" eyebrow="Kode 429" title="Terlalu banyak percobaan">
    <p>Kamu mencoba terlalu sering dalam waktu singkat. Tunggu sekitar satu menit lalu coba lagi.</p>
    <div class="rs-status__actions">
        <a href="{{ url()->previous() }}" class="rs-btn rs-btn--block">Kembali ke halaman sebelumnya</a>
    </div>
</x-rs.status>
@endsection

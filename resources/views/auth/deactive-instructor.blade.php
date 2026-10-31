@extends('layouts.plain-layout')

@section('title', 'Akun tidak aktif')

@section('content')
<x-rs.status tone="danger" icon="bi-person-x" title="Akun instruktur dinonaktifkan">
    <p>
        Yth. Instruktur, akun Anda telah dinonaktifkan oleh tim kami.
        Keputusan ini diambil demi menjaga kenyamanan dan keamanan seluruh pengguna.
        Terima kasih atas waktu dan dedikasi Anda bersama kami.
    </p>
    <div class="rs-status__actions">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="rs-btn rs-btn--outline rs-btn--block">Keluar</button>
        </form>
    </div>
</x-rs.status>
@endsection

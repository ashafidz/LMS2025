@extends('layouts.plain-layout')

@section('title', 'Pendaftaran ditolak')

@section('content')
<x-rs.status tone="danger" icon="bi-x-octagon" title="Pendaftaran instruktur ditolak">
    <p>
        Yth. Instruktur, setelah kami melakukan peninjauan, kami ingin menginformasikan bahwa akun Instruktur Anda telah ditolak oleh tim kami.
        Keputusan ini diambil demi menjaga kualitas dan kenyamanan seluruh pengguna.
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

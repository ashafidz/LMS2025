@extends('layouts.plain-layout')

@section('title', 'Akun tidak aktif')

@section('content')
<x-rs.status tone="danger" icon="bi-person-x" title="Akun siswa tidak aktif">
    <p>
        Hai, kami ingin memberi tahu bahwa akun belajar Anda saat ini tidak aktif.
        Hal ini dilakukan demi menjaga kenyamanan dan keamanan semua pengguna.
        Terima kasih sudah menjadi bagian dari komunitas belajar kami, semoga bisa segera kembali aktif!
    </p>
    <div class="rs-status__actions">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="rs-btn rs-btn--outline rs-btn--block">Keluar</button>
        </form>
    </div>
</x-rs.status>
@endsection

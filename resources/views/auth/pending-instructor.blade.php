@extends('layouts.plain-layout')

@section('title', 'Pendaftaran sedang ditinjau')

@section('content')
<x-rs.status tone="wait" icon="bi-hourglass-split" title="Pendaftaran instruktur sedang ditinjau">
    <p>
        Terima kasih sudah memverifikasi email. Pendaftaran kamu sebagai instruktur sedang ditinjau oleh tim kami. Kami akan mengabari lewat email setelah ada keputusan.
    </p>
    <div class="rs-status__actions">
        <a href="{{ route('role.switch', 'student') }}" class="rs-btn rs-btn--block">Buka dasbor siswa</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="rs-btn rs-btn--outline rs-btn--block">Keluar</button>
        </form>
    </div>
</x-rs.status>
@endsection

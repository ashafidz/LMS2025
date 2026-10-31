@extends('layouts.home-layout')

@section('title', 'Cek Keaslian Sertifikat - Ruang Studi')

@section('content')
<div class="rs-page">
    <section class="rs-pagehead">
        <div class="rs-wrap">
            <p class="rs-crumbs"><a href="{{ route('home') }}">Beranda</a> / Cek Keaslian Sertifikat</p>
            <h1 class="rs-h1">Cek Keaslian Sertifikat</h1>
            <p class="rs-lead">
                Masukkan kode yang tertera pada sertifikat (contoh: LMS-12-345-1761400000) untuk memastikan sertifikat
                tersebut benar diterbitkan oleh Ruang Studi.
            </p>
        </div>
    </section>

    <section class="rs-section">
        <div class="rs-wrap rs-verify">
            <form method="GET" action="{{ route('certificates.verify') }}" class="rs-form">
                <div class="rs-field">
                    <label for="kode">Kode sertifikat</label>
                    <div class="rs-verify__row">
                        <input type="text" id="kode" name="kode" value="{{ $code }}" maxlength="100" required
                               class="rs-input" placeholder="Ketik atau tempel kode sertifikat" autocomplete="off">
                        <button type="submit" class="rs-btn">Cek</button>
                    </div>
                </div>
            </form>

            @if ($searched && $certificate)
                <div class="rs-verify__result rs-verify__result--ok" role="status">
                    <h2 class="rs-h2"><i class="bi bi-patch-check-fill" aria-hidden="true"></i> Sertifikat asli</h2>
                    <p>Sertifikat dengan kode ini benar diterbitkan oleh Ruang Studi.</p>
                    <dl>
                        <div><dt>Nama penerima</dt><dd>{{ $certificate->user->name }}</dd></div>
                        <div><dt>Kursus</dt><dd>{{ $certificate->course->title ?? '-' }}</dd></div>
                        <div><dt>Instruktur</dt><dd>{{ $certificate->course?->instructor?->name ?? '-' }}</dd></div>
                        <div><dt>Tanggal terbit</dt><dd>{{ $issuedDate }}</dd></div>
                        <div><dt>Kode sertifikat</dt><dd>{{ $certificate->certificate_code }}</dd></div>
                    </dl>
                </div>
            @elseif ($searched)
                <div class="rs-verify__result rs-verify__result--danger" role="alert">
                    <h2 class="rs-h2"><i class="bi bi-x-circle-fill" aria-hidden="true"></i> Kode sertifikat tidak ditemukan</h2>
                    <p>
                        Periksa kembali penulisan kode (huruf, angka, dan tanda strip). Jika kode sudah benar tetapi tetap
                        tidak ditemukan, sertifikat tersebut tidak diterbitkan oleh Ruang Studi.
                    </p>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection

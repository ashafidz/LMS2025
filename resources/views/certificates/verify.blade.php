@extends('layouts.home-layout')

@section('title', 'Cek Keaslian Sertifikat - Ruang Studi')

@section('content')
<section class="section light-background" style="padding-top: 120px; min-height: 70vh;">
    <div class="container" style="max-width: 720px;">
        <h2 class="mb-2">Cek Keaslian Sertifikat</h2>
        <p class="text-muted mb-4">
            Masukkan kode yang tertera pada sertifikat (contoh: LMS-12-345-1761400000) untuk memastikan sertifikat
            tersebut benar diterbitkan oleh Ruang Studi.
        </p>

        <form method="GET" action="{{ route('certificates.verify') }}" class="mb-4">
            <label for="kode" class="form-label fw-semibold">Kode sertifikat</label>
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" id="kode" name="kode" value="{{ $code }}" maxlength="100" required
                       class="form-control" style="flex: 1 1 260px;" placeholder="Ketik atau tempel kode sertifikat" autocomplete="off">
                <button type="submit" class="btn btn-primary px-4">Cek</button>
            </div>
        </form>

        @if ($searched && $certificate)
            <div class="p-4 rounded border border-success" style="background: #f0fbf4;">
                <h4 class="text-success mb-3"><i class="bi bi-patch-check-fill"></i> Sertifikat asli</h4>
                <p class="mb-3">Sertifikat dengan kode ini benar diterbitkan oleh Ruang Studi.</p>
                <table class="table table-borderless mb-0" style="background: transparent;">
                    <tr><th style="width: 40%;">Nama penerima</th><td>{{ $certificate->user->name }}</td></tr>
                    <tr><th>Kursus</th><td>{{ $certificate->course->title ?? '-' }}</td></tr>
                    <tr><th>Instruktur</th><td>{{ $certificate->course?->instructor?->name ?? '-' }}</td></tr>
                    <tr><th>Tanggal terbit</th><td>{{ $issuedDate }}</td></tr>
                    <tr><th>Kode sertifikat</th><td>{{ $certificate->certificate_code }}</td></tr>
                </table>
            </div>
        @elseif ($searched)
            <div class="p-4 rounded border border-danger" style="background: #fdf2f2;">
                <h4 class="text-danger mb-2"><i class="bi bi-x-circle-fill"></i> Kode sertifikat tidak ditemukan</h4>
                <p class="mb-0">
                    Periksa kembali penulisan kode (huruf, angka, dan tanda strip). Jika kode sudah benar tetapi tetap
                    tidak ditemukan, sertifikat tersebut tidak diterbitkan oleh Ruang Studi.
                </p>
            </div>
        @endif
    </div>
</section>
@endsection

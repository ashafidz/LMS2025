@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Template Sertifikat</h5>
                        <p class="m-b-0">Atur tampilan sertifikat yang diterima siswa. Setiap perubahan disimpan sebagai versi baru.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Template Sertifikat</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="card">
                        <div class="card-block">
                            @if ($activeTemplate)
                                <div class="alert alert-success mb-0">
                                    <i class="fa fa-check-circle"></i>
                                    Sertifikat yang aktif: <strong>{{ $activeTemplate->name }} (Versi {{ $activeTemplate->version }})</strong>,
                                    diaktifkan {{ $activeTemplate->activated_at?->format('d M Y H:i') }}
                                    @if ($activeTemplate->activator) oleh {{ $activeTemplate->activator->name }} @endif.
                                </div>
                            @else
                                <div class="alert alert-info mb-0">
                                    <i class="fa fa-info-circle"></i>
                                    Belum ada template aktif. Sertifikat memakai desain bawaan.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5>Daftar Versi</h5>
                            <div class="card-header-right" style="position: static; float: right;">
                                @if ($activeTemplate)
                                    <form action="{{ route($routePrefix . '.certificate-templates.deactivate') }}" method="POST" class="d-inline js-confirm"
                                        data-message="Sertifikat akan kembali memakai desain bawaan. Lanjutkan?">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-secondary">Kembali ke Desain Bawaan</button>
                                    </form>
                                @endif
                                <a href="{{ route($routePrefix . '.certificate-templates.create') }}" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Buat Template Baru
                                </a>
                            </div>
                        </div>
                        <div class="card-block">
                            @if ($templates->isEmpty())
                                <p class="text-muted text-center m-t-20 m-b-20">Belum ada template. Klik Buat Template Baru untuk mulai.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Pratinjau</th>
                                                <th>Versi</th>
                                                <th>Catatan</th>
                                                <th>Dibuat oleh</th>
                                                <th>Tanggal</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($templates->groupBy('name') as $designName => $versions)
                                                <tr class="table-active">
                                                    <td colspan="7"><strong>{{ $designName }}</strong>
                                                        <span class="text-muted">({{ $versions->count() }} versi)</span></td>
                                                </tr>
                                                @foreach ($versions as $template)
                                                    <tr>
                                                        <td>
                                                            <img src="{{ $template->backgroundUrl() }}" alt="Pratinjau {{ $template->name }}"
                                                                style="width: 90px; height: auto; border: 1px solid #ddd;">
                                                        </td>
                                                        <td>Versi {{ $template->version }}</td>
                                                        <td>{{ $template->change_note ?: '-' }}</td>
                                                        <td>{{ $template->creator?->name ?? '-' }}</td>
                                                        <td>{{ $template->created_at?->format('d M Y H:i') }}</td>
                                                        <td>
                                                            @if ($template->is_active)
                                                                <span class="badge badge-success">Aktif</span>
                                                            @else
                                                                <span class="badge badge-secondary">Tidak aktif</span>
                                                            @endif
                                                        </td>
                                                        <td style="white-space: nowrap;">
                                                            <a href="{{ route($routePrefix . '.certificate-templates.preview', $template) }}" target="_blank" class="btn btn-sm btn-outline-info">Pratinjau PDF</a>
                                                            <a href="{{ route($routePrefix . '.certificate-templates.edit', $template) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                                                            @unless ($template->is_active)
                                                                <form action="{{ route($routePrefix . '.certificate-templates.activate', $template) }}" method="POST" class="d-inline js-confirm"
                                                                    data-message="Semua sertifikat yang diunduh siswa setelah ini akan memakai desain ini. Lanjutkan?">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="btn btn-sm btn-success">Jadikan Aktif</button>
                                                                </form>
                                                                <form action="{{ route($routePrefix . '.certificate-templates.destroy', $template) }}" method="POST" class="d-inline js-confirm"
                                                                    data-message="Versi {{ $template->version }} akan dihapus dan tidak bisa dikembalikan. Lanjutkan?">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                                                </form>
                                                            @endunless
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Konfirmasi sebelum aksi yang berdampak (aktifkan, hapus, kembali ke desain bawaan)
    document.querySelectorAll('form.js-confirm').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: form.dataset.message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush

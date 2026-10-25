@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Badge</h5>
                        <p class="m-b-0">Atur badge dan syarat untuk mendapatkannya. Badge diberikan otomatis begitu siswa memenuhi syarat.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Badge</a></li>
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
                    @if (session('warning'))
                        <div class="alert alert-warning">{{ session('warning') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="card">
                        <div class="card-header">
                            <h5>Daftar Badge</h5>
                            <div class="card-header-right">
                                <a href="{{ route($routePrefix . '.badges.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus"></i> Buat Badge Baru
                                </a>
                            </div>
                        </div>
                        <div class="card-block">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Ikon</th>
                                            <th>Badge</th>
                                            <th>Syarat</th>
                                            <th class="text-center">Sudah didapat</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($badges as $badge)
                                            <tr>
                                                <td><img src="{{ asset('storage/' . $badge->icon_path) }}" alt="{{ $badge->title }}" style="width: 40px; height: 40px; object-fit: cover;"></td>
                                                <td>
                                                    <strong>{{ $badge->title }}</strong>
                                                    <div class="text-muted small">{{ \Illuminate\Support\Str::limit($badge->description, 60) }}</div>
                                                </td>
                                                <td>
                                                    @forelse ($badge->rules as $rule)
                                                        <div>{{ $rule->sentence() }}</div>
                                                    @empty
                                                        <span class="text-muted">Belum ada syarat (tidak diberikan otomatis)</span>
                                                    @endforelse
                                                </td>
                                                <td class="text-center">{{ $badge->users_count }} siswa</td>
                                                <td>
                                                    @if ($badge->is_active)
                                                        <label class="label label-success">Aktif</label>
                                                    @else
                                                        <label class="label label-default">Tidak Aktif</label>
                                                    @endif
                                                </td>
                                                <td style="min-width: 170px;">
                                                    <a href="{{ route($routePrefix . '.badges.edit', $badge) }}" class="btn btn-info btn-sm btn-block mb-1" title="Ubah"><i class="fa fa-pencil"></i> Ubah</a>

                                                    <form action="{{ route($routePrefix . '.badges.duplicate', $badge) }}" method="POST" class="d-block mb-1 js-confirm"
                                                          data-message="Membuat salinan badge ini (tanpa pemilik, nonaktif) agar syaratnya bisa diubah.">
                                                        @csrf
                                                        <button type="submit" class="btn btn-secondary btn-sm btn-block" title="Duplikat"><i class="fa fa-copy"></i> Duplikat</button>
                                                    </form>

                                                    @if ($badge->is_active && $badge->rules->isNotEmpty())
                                                        <form action="{{ route($routePrefix . '.badges.backfill', $badge) }}" method="POST" class="d-block mb-1 js-confirm"
                                                              data-message="Siswa yang sudah memenuhi syarat saat ini akan langsung mendapat badge ini.">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-sm btn-block" title="Berikan ke siswa yang sudah memenuhi syarat"><i class="fa fa-gift"></i> Berikan ke siswa</button>
                                                        </form>
                                                    @endif

                                                    <form action="{{ route($routePrefix . '.badges.destroy', $badge) }}" method="POST" class="d-block mb-1 js-confirm"
                                                          data-message="{{ $badge->users_count > 0 ? 'Badge ini sudah dimiliki ' . $badge->users_count . ' siswa dan akan hilang dari mereka.' : 'Badge akan dihapus.' }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm btn-block" title="Hapus"><i class="fa fa-trash"></i> Hapus</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Belum ada badge. Klik Buat Badge Baru untuk mulai.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center">
                                {{ $badges->links() }}
                            </div>
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
    // Konfirmasi sebelum aksi yang berdampak (duplikat, berikan ke siswa, hapus)
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

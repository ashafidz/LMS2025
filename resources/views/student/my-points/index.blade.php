@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Poin Saya</h5>
                        <p class="m-b-0">Lihat total poin dan riwayat perolehan poin Anda di setiap kursus.</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Poin & Riwayat</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">
                        {{-- KIRI: Summary & Info (col-lg-4) --}}
                        <div class="col-lg-4">
                            <div class="row">
                                {{-- Card: Total Poin --}}
                                <div class="col-12 col-md-6 col-lg-12">
                                    <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px;">
                                        <div class="card-block text-center p-4">
                                            <i class="fa fa-medal text-warning f-50 mb-3"></i>
                                            <h2 class="font-weight-bold text-warning mb-1">{{ number_format($totalPoints, 0, ',', '.') }} <small class="text-muted f-16">Poin</small></h2>
                                            <p class="text-muted mb-0">Total Akumulasi Poin Anda</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card: Saldo Diamond --}}
                                <div class="col-12 col-md-6 col-lg-12">
                                    <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px;">
                                        <div class="card-block text-center p-4">
                                            <i class="fa fa-diamond text-c-blue d-block f-50 mb-3"></i>
                                            <h2 class="font-weight-bold text-c-blue mb-1">{{ number_format($user->diamond_balance, 0, ',', '.') }} <small class="text-muted f-16">Diamond</small></h2>
                                            <p class="text-muted mb-3">Saldo Diamond Anda Saat Ini</p>
                                            <a href="{{ route('courses') }}" class="btn btn-primary btn-sm btn-round px-4 shadow-sm"><i class="fa fa-shopping-cart me-1"></i> Gunakan Diamond</a>
                                            
                                            <div class="mt-3 pt-3 border-top text-left">
                                                <span class="text-muted" style="font-size: 12px;"><i class="fa fa-exchange mr-1"></i> Rasio Konversi Otomatis:</span><br>
                                                <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $siteSettings->point_to_diamond_rate ? number_format($siteSettings->point_to_diamond_rate, 0, ',', '.') : '0' }} Poin = 1 Diamond</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Cara Mendapatkan Poin (Compact Badges) --}}
                            <div class="card shadow-sm mb-4" style="border-radius: 12px;">
                                <div class="card-header border-bottom-0 pb-0">
                                    <h6 class="font-weight-bold"><i class="fa fa-info-circle text-info mr-1"></i> Cara Mendapat Poin</h6>
                                </div>
                                <div class="card-block pt-3">
                                    <div class="d-flex flex-column gap-2" style="gap: 8px;">
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                            <span class="text-muted" style="font-size: 13px;">Beli Kursus</span>
                                            <span class="badge badge-light-success text-success font-weight-bold">+{{ $siteSettings->points_for_purchase ? number_format($siteSettings->points_for_purchase, 0, ',', '.') : '0' }} Poin</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                            <span class="text-muted" style="font-size: 13px;">Baca Artikel</span>
                                            <span class="badge badge-light-success text-success font-weight-bold">+{{ $siteSettings->points_for_article ? number_format($siteSettings->points_for_article, 0, ',', '.') : '0' }} Poin</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                            <span class="text-muted" style="font-size: 13px;">Tonton Video</span>
                                            <span class="badge badge-light-success text-success font-weight-bold">+{{ $siteSettings->points_for_video ? number_format($siteSettings->points_for_video, 0, ',', '.') : '0' }} Poin</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                            <span class="text-muted" style="font-size: 13px;">Baca Dokumen</span>
                                            <span class="badge badge-light-success text-success font-weight-bold">+{{ $siteSettings->points_for_document ? number_format($siteSettings->points_for_document, 0, ',', '.') : '0' }} Poin</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                            <span class="text-muted" style="font-size: 13px;">Lulus Kuis</span>
                                            <span class="badge badge-light-success text-success font-weight-bold">+{{ $siteSettings->points_for_quiz ? number_format($siteSettings->points_for_quiz, 0, ',', '.') : '0' }} Poin</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                            <span class="text-muted" style="font-size: 13px;">Kumpul Tugas</span>
                                            <span class="badge badge-light-success text-success font-weight-bold">+{{ $siteSettings->points_for_assignment ? number_format($siteSettings->points_for_assignment, 0, ',', '.') : '0' }} Poin</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                            <span class="text-muted" style="font-size: 13px;">Isi Polling</span>
                                            <span class="badge badge-light-success text-success font-weight-bold">+{{ $siteSettings->points_for_polling ? number_format($siteSettings->points_for_polling, 0, ',', '.') : '0' }} Poin</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted" style="font-size: 13px;">Isi Word Cloud</span>
                                            <span class="badge badge-light-success text-success font-weight-bold">+{{ $siteSettings->points_for_wordcloud ? number_format($siteSettings->points_for_wordcloud, 0, ',', '.') : '0' }} Poin</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- KANAN: Aktivitas & Riwayat (col-lg-8) --}}
                        <div class="col-lg-8">
                            {{-- Chart --}}
                            <div class="card shadow-sm mb-4" style="border-radius: 12px;">
                                <div class="card-block">
                                    <h6 class="font-weight-bold mb-1">Aktivitas Poin Anda</h6>
                                    <p class="text-muted mb-4" style="font-size: 13px;">Grafik perolehan dan penggunaan poin Anda selama 12 bulan terakhir.</p>
                                    <div style="height: 250px;">
                                        <canvas id="myLineChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabbed History (Riwayat Poin & Diamond) --}}
                            <div class="card shadow-sm" style="border-radius: 12px;">
                                <div class="card-header p-b-0 border-bottom-0">
                                    <ul class="nav nav-tabs md-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active font-weight-bold" data-toggle="tab" href="#tab-riwayat-poin" role="tab"><i class="fa fa-medal mr-1"></i> Riwayat Poin</a>
                                            <div class="slide"></div>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link font-weight-bold" data-toggle="tab" href="#tab-riwayat-diamond" role="tab"><i class="fa fa-diamond mr-1"></i> Riwayat Diamond</a>
                                            <div class="slide"></div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-block tab-content p-t-20">
                                    {{-- Tab 1: Poin --}}
                                    <div class="tab-pane active" id="tab-riwayat-poin" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Nama Kursus</th>
                                                        <th>Total Poin</th>
                                                        <th class="d-none d-md-table-cell">Status Konversi</th>
                                                        <th class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($pointsPerCourse as $course)
                                                        <tr>
                                                            <td class="align-middle">
                                                                <div class="text-wrap" style="max-width: 200px; word-wrap: break-word;">
                                                                    <strong>{{ $course->title }}</strong>
                                                                    <div class="d-block d-md-none mt-1">
                                                                        @if($course->pivot->is_converted_to_diamond)
                                                                            <label class="label label-success mb-0">Dikonversi</label>
                                                                        @else
                                                                            <label class="label label-default mb-0">Belum</label>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="align-middle"><strong><span class="text-warning">{{ $course->pivot->points_earned }}</span> Poin</strong></td>
                                                            <td class="d-none d-md-table-cell align-middle">
                                                                @if($course->pivot->is_converted_to_diamond)
                                                                    <label class="label label-success mb-0">Sudah Dikonversi</label>
                                                                @else
                                                                    <label class="label label-default mb-0">Belum Dikonversi</label>
                                                                @endif
                                                            </td>
                                                            <td class="text-center align-middle" style="width: 1%; white-space: nowrap;">
                                                                <button class="btn btn-outline-primary btn-sm btn-round" data-toggle="modal" data-target="#historyModal-{{ $course->id }}">
                                                                    <i class="fa fa-list mr-1"></i> Rincian
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center text-muted py-4">
                                                                <i class="fa fa-info-circle d-block f-24 mb-2"></i>
                                                                Anda belum mendapatkan poin dari kursus manapun.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-center mt-3">
                                            {{ $pointsPerCourse->links() }}
                                        </div>
                                    </div>

                                    {{-- Tab 2: Diamond --}}
                                    <div class="tab-pane" id="tab-riwayat-diamond" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Tanggal</th>
                                                        <th>Deskripsi</th>
                                                        <th class="text-right">Jumlah</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($diamondHistories as $history)
                                                        <tr>
                                                            <td class="align-middle text-muted" style="white-space: nowrap;">{{ $history->created_at->format('d M Y, H:i') }}</td>
                                                            <td class="align-middle text-wrap" style="min-width: 150px;">{{ $history->description }}</td>
                                                            <td class="text-right align-middle" style="white-space: nowrap;">
                                                                @if($history->diamonds > 0)
                                                                    <span class="text-success font-weight-bold" style="font-size: 16px;">+{{ $history->diamonds }}</span>
                                                                @else
                                                                    <span class="text-danger font-weight-bold" style="font-size: 16px;">{{ $history->diamonds }}</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center text-muted py-4">
                                                                <i class="fa fa-diamond d-block f-24 mb-2"></i>
                                                                Anda belum memiliki riwayat diamond.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-center mt-3">
                                            {{ $diamondHistories->links() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Riwayat --}}
                    @foreach($pointsPerCourse as $course)
                    <div class="modal fade" id="historyModal-{{ $course->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Riwayat Poin: {{ $course->title }}</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <ul class="list-group list-group-flush">
                                        @forelse($pointHistories[$course->id] ?? [] as $history)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    {{ $history->description }}<br>
                                                    <small class="text-muted">{{ $history->created_at->format('d M Y, H:i') }}</small>
                                                </div>
                                                <span class="badge badge-primary badge-pill">+{{ $history->points }}</span>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-muted">Tidak ada riwayat untuk kursus ini.</li>
                                        @endforelse
                                    </ul>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Library Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('myLineChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Poin Diperoleh',
                        data: @json($chartDataEarned),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Poin Digunakan',
                        data: @json($chartDataSpent),
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        // --- BAGIAN BARU UNTUK MERINGKAS SKALA ---
                        ticks: {
                            // Batasi jumlah "garis" di sumbu Y agar tidak terlalu padat.
                            // Chart.js akan secara otomatis menghitung kelipatan yang bagus (misal: 0, 50, 100).
                            maxTicksLimit: 6 
                        }
                        // --- AKHIR BAGIAN BARU ---
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                }
            }
        });
    }
});
</script>
@endpush
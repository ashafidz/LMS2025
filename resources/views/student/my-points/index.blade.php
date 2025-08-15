@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Poin Saya</h5>
                        <p class="m-b-0">Lihat total poin dan riwayat perolehan poin Anda di setiap kursus.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Poinku</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    {{-- Bagian Atas: Poin Saya & Rincian Poin (2 kolom) --}}
                    <div class="row">
                        {{-- KIRI: Poin Saya + Cara Mendapatkan Poin --}}
                        <div class="col-md-4">
                            {{-- Card: Total Poin --}}
                            <div class="card widget-visitor-card mb-4">
                                <div class="card-block-big text-center">
                                    <i class="ti-medall-alt text-warning f-40"></i>
                                    <h4 class="m-t-20"><span class="text-warning">{{ number_format($totalPoints, 0, ',', '.') }}</span> Poin</h4>
                                    <p>Total Akumulasi Poin anda saat ini</p>
                                </div>
                            </div>
                            
                            {{-- Card: Line Chart --}}
                            <div class="card shadow-sm mb-4">
                                <div class="card-block" style="height:283px;">
                                    <!-- Judul -->
                                    <h6 class="fw-bold mb-1">Line Chart</h6>
                                    <p class="text-muted mb-4">
                                        lorem ipsum dolor sit amet, consectetur adipisicing elit
                                    </p> 
                            
                                    <!-- Grafik -->
                                    <canvas id="myLineChart" style="height:300px;"></canvas>
                                </div>
                            </div>
                            
                            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                            <script>
                                const ctx = document.getElementById('myLineChart').getContext('2d');
                            
                                new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: ['2006', '2007', '2008', '2009', '2010', '2011', '2012'],
                                        datasets: [
                                            {
                                                data: [100, 75, 50, 75, 50, 75, 100],
                                                borderColor: '#99ABCB',
                                                tension: 0.4
                                            },
                                            {
                                                data: [90, 65, 40, 65, 40, 65, 90],
                                                borderColor: '#FF9F40',
                                                tension: 0.4
                                            }
                                        ]
                                    }
                                });
                            </script>

                    
                            {{-- Card: Cara Mendapatkan Poin --}}
                            <div class="card">
                                <div class="card-header">
                                    <h5>Cara Mendapatkan Poin</h5>
                                </div>
                                <div class="card-block">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Membeli Kursus Dengan Uang <span class="badge badge-success">{{ $siteSettings->points_for_purchase ? number_format($siteSettings->points_for_purchase, 0, ',', '.') : '0' }} Poin</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Menyelesaikan Lesson Tipe Article <span class="badge badge-success">{{ $siteSettings->points_for_article ? number_format($siteSettings->points_for_article, 0, ',', '.') : '0' }} Poin</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Menyelesaikan Lesson Tipe Video <span class="badge badge-success">{{ $siteSettings->points_for_video ? number_format($siteSettings->points_for_video, 0, ',', '.') : '0' }} Poin</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Menyelesaikan Lesson Tipe Dokumen <span class="badge badge-success">{{ $siteSettings->points_for_document ? number_format($siteSettings->points_for_document, 0, ',', '.') : '0' }} Poin</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Menyelesaikan Lesson Tipe Quiz <span class="badge badge-success">{{ $siteSettings->points_for_quiz ? number_format($siteSettings->points_for_quiz, 0, ',', '.') : '0' }} Poin</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Menyelesaikan Lesson Tipe Assignment <span class="badge badge-success">{{ $siteSettings->points_for_assignment ? number_format($siteSettings->points_for_assignment, 0, ',', '.') : '0' }} Poin</span>
                                        </li>
                                        {{-- <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Menyelesaikan Lesson Tipe Video <span class="badge badge-success">+2</span>
                                        </li> --}}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    
                        {{-- KANAN: Rincian Poin + Diamond Saya + Riwayat Diamond --}}
                        <div class="col-md-8">
                            {{-- Card: Rincian Poin --}}
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Rincian Poin per Kursus</h5>
                                </div>
                                <div class="card-block table-border-style">
                                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Nama Kursus</th>
                                                    <th>Poin</th>
                                                    <th>Status</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($pointsPerCourse as $course)
                                                    <tr>
                                                        <td>{{ $course->title }}</td>
                                                        <td><strong>{{ $course->pivot->points_earned }} Poin</strong></td>
                                                        <td>
                                                            @if($course->pivot->is_converted_to_diamond)
                                                                <label class="label label-success">Sudah Dikonversi</label>
                                                            @else
                                                                <label class="label label-default">Belum Dikonversi</label>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <button class="btn btn-inverse btn-sm" data-toggle="modal" data-target="#historyModal-{{ $course->id }}">
                                                                Lihat Riwayat
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center">Anda belum mendapatkan poin dari kursus manapun.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $pointsPerCourse->links() }}
                                    </div>
                                </div>
                            </div>
                    
                            {{-- Saldo Diamond --}}
                            <div class="card mb-4">
                                <div class="card-block text-center">
                                    <i class="fa fa-diamond text-c-blue d-block f-40"></i>
                                        <h4 class="m-t-20"><span class="text-c-blue">{{ number_format($user->diamond_balance, 0, ',', '.') }}</span> Diamond</h4>
                                    <p class="m-b-20">Saldo Diamond Anda Saat Ini</p>
                                    <a href="{{ route('courses') }}" class="btn btn-primary btn-sm btn-round">Gunakan Diamond</a>
                                </div>
                            </div>
                    
                            {{-- Riwayat Diamond --}}
                            <div class="card">
                                <div class="card-header">
                                    <h5>Riwayat Diamond</h5>
                                </div>
                                <div class="card-block table-border-style">
                                    <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
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
                                                        <td>{{ $history->created_at->format('d M Y, H:i') }}</td>
                                                        <td>{{ $history->description }}</td>
                                                        <td class="text-right">
                                                            @if($history->diamonds > 0)
                                                                <span class="text-success font-weight-bold">+{{ $history->diamonds }}</span>
                                                            @else
                                                                <span class="text-danger font-weight-bold">{{ $history->diamonds }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center">Anda belum memiliki riwayat diamond.</td>
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

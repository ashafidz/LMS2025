@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Analisis K-Means Profil Siswa</h5>
                        <p class="m-b-0">Kursus: <strong>{{ $course->title }}</strong></p>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <form action="{{ route('instructor.kmeans.run', $course) }}" method="POST" class="d-inline" onsubmit="return confirm('Jalankan analisis K-Means sekarang? Hasil sebelumnya akan digantikan.');">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fa fa-play"></i> Jalankan Analisis
                        </button>
                    </form>
                    <a href="{{ route('instructor.courses.index') }}" class="btn btn-secondary btn-sm ml-1">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif

                    @if(!$latestRun)
                        {{-- Belum ada data K-Means --}}
                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <div class="card border-warning text-center">
                                    <div class="card-body py-5">
                                        <i class="fa fa-bar-chart fa-4x text-warning mb-3"></i>
                                        <h5>Analisis Belum Tersedia</h5>
                                        <p class="text-muted">Superadmin belum menjalankan analisis K-Means untuk kursus ini, atau belum ada siswa yang menyelesaikan tes profiling.</p>
                                        <p class="text-muted small">Hubungi Superadmin untuk menjalankan analisis.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Informasi Run --}}
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    <i class="fa fa-info-circle"></i>
                                    Analisis terakhir dijalankan pada <strong>{{ $latestRun->created_at->format('d M Y, H:i') }}</strong>
                                    dengan jumlah kluster <strong>K = {{ $latestRun->k_value }}</strong>.
                                    Halaman ini hanya menampilkan hasil (read-only).
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Kolom Kiri: Ringkasan Kluster --}}
                            <div class="col-md-4">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <h6 class="text-primary"><i class="fa fa-users"></i> Data Siswa per Kluster</h6>
                                        <hr>
                                        @foreach(collect($chartData) as $clusterInfo)
                                            <div class="mb-3">
                                                <h6 style="color: {{ $clusterInfo['backgroundColor'] }}">
                                                    {{ $clusterInfo['label'] }}
                                                    <span class="badge badge-secondary ml-1">{{ count($clusterInfo['data']) }} Siswa</span>
                                                </h6>
                                                <ul class="list-unstyled ml-3 small">
                                                    @foreach($clusterInfo['data'] as $dp)
                                                        <li><i class="fa fa-user-o text-muted mr-1"></i> {{ $dp['student_name'] }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom Kanan: Scatter Plot --}}
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fa fa-line-chart"></i> Visualisasi Scatter Plot
                                            <small class="text-muted">(Mastery Goal vs Prior Knowledge)</small>
                                        </h5>
                                    </div>
                                    <div class="card-block">
                                        <canvas id="kmeansChart" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tabel Centroid --}}
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card border-info">
                                    <div class="card-header bg-light">
                                        <h5 class="text-info mb-0"><i class="fa fa-cubes"></i> Titik Pusat Kluster (Centroid) — 10 Dimensi</h5>
                                        <small class="text-muted">Nilai rata-rata seluruh dimensi profil pada setiap kluster.</small>
                                    </div>
                                    <div class="card-block table-border-style">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm text-center">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th rowspan="2" class="align-middle">Kluster</th>
                                                        <th colspan="2">Goal Setting (%)</th>
                                                        <th rowspan="2" class="align-middle bg-light text-primary">Prior Knowledge (%)</th>
                                                        <th colspan="3">SDT / Motivasi (%)</th>
                                                        <th colspan="4">AI Preferences (1-5)</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Mastery</th><th>Performance</th>
                                                        <th>Autonomy</th><th>Competence</th><th>Relatedness</th>
                                                        <th>Transp.</th><th>Guidance</th><th>Adaptivity</th><th>Feedback</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($clusterCentroids as $clusterNum => $centroid)
                                                    <tr>
                                                        <td class="font-weight-bold">Kluster {{ $clusterNum }}</td>
                                                        <td>{{ $centroid['mastery'] }}</td>
                                                        <td>{{ $centroid['performance'] }}</td>
                                                        <td class="font-weight-bold text-primary bg-light">{{ $centroid['knowledge'] }}</td>
                                                        <td>{{ $centroid['autonomy'] }}</td>
                                                        <td>{{ $centroid['competence'] }}</td>
                                                        <td>{{ $centroid['relatedness'] }}</td>
                                                        <td>{{ $centroid['transparency'] }}</td>
                                                        <td>{{ $centroid['guidance'] }}</td>
                                                        <td>{{ $centroid['adaptivity'] }}</td>
                                                        <td>{{ $centroid['feedback'] }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tabel Raw Score Siswa --}}
                                <div class="card mt-3 border-secondary">
                                    <div class="card-header bg-light">
                                        <h5 class="text-secondary mb-0"><i class="fa fa-table"></i> Skor Individual Siswa (10 Dimensi)</h5>
                                    </div>
                                    <div class="card-block table-border-style">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped table-sm text-center">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="text-left align-middle">Nama Siswa</th>
                                                        <th class="align-middle">Kluster</th>
                                                        <th>Mastery</th><th>Perform.</th>
                                                        <th class="text-primary bg-light">Knowledge</th>
                                                        <th>Autonomy</th><th>Competence</th><th>Relatedness</th>
                                                        <th>Transp.</th><th>Guidance</th><th>Adaptivity</th><th>Feedback</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach(collect($tableData)->sortBy('cluster') as $row)
                                                    <tr>
                                                        <td class="text-left">{{ $row['student_name'] }}</td>
                                                        <td><span class="badge badge-primary">Kluster {{ $row['cluster'] }}</span></td>
                                                        <td>{{ round($row['scores']['mastery'], 2) }}</td>
                                                        <td>{{ round($row['scores']['performance'], 2) }}</td>
                                                        <td class="font-weight-bold text-primary bg-light">{{ round($row['scores']['knowledge'], 2) }}</td>
                                                        <td>{{ round($row['scores']['autonomy'], 2) }}</td>
                                                        <td>{{ round($row['scores']['competence'], 2) }}</td>
                                                        <td>{{ round($row['scores']['relatedness'], 2) }}</td>
                                                        <td>{{ round($row['scores']['transparency'], 2) }}</td>
                                                        <td>{{ round($row['scores']['guidance'], 2) }}</td>
                                                        <td>{{ round($row['scores']['adaptivity'], 2) }}</td>
                                                        <td>{{ round($row['scores']['feedback'], 2) }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if($latestRun)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartDataRaw = @json($chartData);
    chartDataRaw.forEach(dataset => {
        dataset.pointRadius = 7;
        dataset.pointHoverRadius = 10;
    });

    new Chart(document.getElementById('kmeansChart').getContext('2d'), {
        type: 'scatter',
        data: { datasets: chartDataRaw },
        options: {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const p = context.raw;
                            return `${p.student_name} (X: ${p.x.toFixed(2)}, Y: ${p.y.toFixed(2)})`;
                        }
                    }
                }
            },
            scales: {
                x: { title: { display: true, text: 'Skor Mastery Goal (X)' } },
                y: { title: { display: true, text: 'Skor Prior Knowledge (Y)' } }
            }
        }
    });
</script>
@endpush
@endif

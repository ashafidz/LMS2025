@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Visualisasi K-Means</h5>
                        <p class="m-b-0">Kursus: {{ $course->title }}</p>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('superadmin.kmeans.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h6 class="text-primary">Informasi Clustering</h6>
                                    <hr>
                                    <p class="mb-1"><strong>Waktu Run:</strong> {{ $latestRun->created_at->format('d M Y H:i') }}</p>
                                    <p class="mb-1"><strong>Jumlah Kluster (K):</strong> {{ $latestRun->k_value }}</p>
                                    <p class="mb-1"><strong>Inertia:</strong> {{ number_format($latestRun->result_summary['elbow_data'][$latestRun->k_value] ?? 0, 2) }}</p>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5>Data Student per Kluster</h5>
                                </div>
                                <div class="card-block">
                                    @foreach(collect($chartData) as $clusterInfo)
                                        <div class="mb-3">
                                            <h6 style="color: {{ $clusterInfo['backgroundColor'] }}">{{ $clusterInfo['label'] }} ({{ count($clusterInfo['data']) }} Siswa)</h6>
                                            <ul class="list-unstyled ml-3 small">
                                                @foreach($clusterInfo['data'] as $dp)
                                                    <li>• {{ $dp['student_name'] }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Visualisasi Scatter Plot (Dimensi Motivasi vs Pengetahuan)</h5>
                                </div>
                                <div class="card-block">
                                    <canvas id="kmeansChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card border-info">
                                <div class="card-header bg-light">
                                    <h5 class="text-info"><i class="fa fa-cubes"></i> Titik Pusat Kluster (Centroid) Multidimensi</h5>
                                    <span class="text-muted d-block mt-1">Nilai rata-rata dari ke-10 dimensi pengukuran pada setiap kluster. Algoritma K-Means menggunakan seluruh angka di bawah ini untuk memisahkan kluster secara adil, bukan hanya 2 dimensi.</span>
                                </div>
                                <div class="card-block table-border-style">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm text-center">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th rowspan="2" class="align-middle">Kluster</th>
                                                    <th colspan="2">Goal Setting (%)</th>
                                                    <th rowspan="2" class="align-middle">Prior Knowledge (%)</th>
                                                    <th colspan="3">SDT / Motivasi (%)</th>
                                                    <th colspan="4">AI Preferences (Skala 1-5)</th>
                                                </tr>
                                                <tr>
                                                    <th>Mastery</th>
                                                    <th>Performance</th>
                                                    <th>Autonomy</th>
                                                    <th>Competence</th>
                                                    <th>Relatedness</th>
                                                    <th>Transparency</th>
                                                    <th>Guidance</th>
                                                    <th>Adaptivity</th>
                                                    <th>Feedback</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($clusterCentroids as $clusterNum => $centroid)
                                                <tr>
                                                    <td class="font-weight-bold">Kluster {{ $clusterNum }}</td>
                                                    <td>{{ $centroid['mastery'] }}</td>
                                                    <td>{{ $centroid['performance'] }}</td>
                                                    <td class="bg-light text-primary font-weight-bold">{{ $centroid['knowledge'] }}</td>
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

                            <div class="card mt-3 border-secondary">
                                <div class="card-header bg-light">
                                    <h5 class="text-secondary"><i class="fa fa-users"></i> Data Mentah (Raw Score) Individual Siswa</h5>
                                </div>
                                <div class="card-block table-border-style">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-sm text-center">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="align-middle text-left">Nama Siswa</th>
                                                    <th class="align-middle">Kluster</th>
                                                    <th class="align-middle" title="Mastery Goal">Mastery</th>
                                                    <th class="align-middle" title="Performance Goal">Perform.</th>
                                                    <th class="align-middle bg-light text-primary" title="Prior Knowledge">Knowledge</th>
                                                    <th class="align-middle" title="Autonomy">Autonomy</th>
                                                    <th class="align-middle" title="Competence">Competence</th>
                                                    <th class="align-middle" title="Relatedness">Relatedness</th>
                                                    <th class="align-middle" title="Transparency">Transp.</th>
                                                    <th class="align-middle" title="Guidance">Guidance</th>
                                                    <th class="align-middle" title="Adaptivity">Adaptivity</th>
                                                    <th class="align-middle" title="Feedback">Feedback</th>
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

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('kmeansChart').getContext('2d');
    
    // Data passed from controller
    const chartDataRaw = @json($chartData);
    
    // Perbesar ukuran titik koordinat
    chartDataRaw.forEach(dataset => {
        dataset.pointRadius = 7;         // Ukuran normal
        dataset.pointHoverRadius = 10;   // Ukuran saat kursor diletakkan di atasnya
    });

    const scatterChart = new Chart(ctx, {
        type: 'scatter',
        data: {
            datasets: chartDataRaw
        },
        options: {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const point = context.raw;
                            return point.student_name + ` (X: ${point.x.toFixed(2)}, Y: ${point.y.toFixed(2)})`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Skor Mastery Goal (X)'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Skor Prior Knowledge (Y)'
                    }
                }
            }
        }
    });
</script>
@endpush

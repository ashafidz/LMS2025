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

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
                                                    <th rowspan="2" class="align-middle text-left">Nama Siswa</th>
                                                    <th rowspan="2" class="align-middle">Tanggal Selesai</th>
                                                    <th rowspan="2" class="align-middle">Kluster Terpilih</th>
                                                    <th colspan="10" class="align-middle">Data Mentah 10 Dimensi</th>
                                                    <th colspan="{{ $latestRun->k_value }}" class="align-middle bg-warning text-dark">Jarak Euclidean (Z-Score)</th>
                                                </tr>
                                                <tr>
                                                    <th title="Mastery Goal">Mastery</th>
                                                    <th title="Performance Goal">Perform.</th>
                                                    <th class="bg-light text-primary" title="Prior Knowledge">Knowledge</th>
                                                    <th title="Autonomy">Autonomy</th>
                                                    <th title="Competence">Competence</th>
                                                    <th title="Relatedness">Relatedness</th>
                                                    <th title="Transparency">Transp.</th>
                                                    <th title="Guidance">Guidance</th>
                                                    <th title="Adaptivity">Adaptivity</th>
                                                    <th title="Feedback">Feedback</th>
                                                    @for($k=1; $k <= $latestRun->k_value; $k++)
                                                        <th class="bg-warning text-dark" title="Jarak ke Pusat Kluster {{ $k }}">Ke K-{{ $k }}</th>
                                                    @endfor
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(collect($tableData)->sortByDesc('date') as $row)
                                                <tr>
                                                    <td class="text-left">{{ $row['student_name'] }}</td>
                                                    <td><small class="text-muted">{{ $row['date'] ? \Carbon\Carbon::parse($row['date'])->format('d M Y, H:i') : '-' }}</small></td>
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
                                                    @for($k=1; $k <= $latestRun->k_value; $k++)
                                                        @php
                                                            $isClosest = ($row['cluster'] == $k);
                                                        @endphp
                                                        <td class="{{ $isClosest ? 'font-weight-bold text-success' : 'text-muted' }}">
                                                            {{ number_format($row['distances'][$k] ?? 0, 3) }}
                                                        </td>
                                                    @endfor
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Theory Accordion -->
                            <div class="card mt-4 border-dark">
                                <div class="card-header bg-dark text-white" data-toggle="collapse" href="#collapseTheory" role="button" aria-expanded="false" aria-controls="collapseTheory" style="cursor: pointer;">
                                    <h5 class="mb-0 text-white"><i class="fa fa-book"></i> Cara Kerja & Rumus Perhitungan K-Means (Klik untuk Buka) <i class="fa fa-chevron-down float-right mt-1"></i></h5>
                                </div>
                                <div class="collapse" id="collapseTheory">
                                    <div class="card-body bg-light">
                                        <h6 class="font-weight-bold text-primary">1. Fase Standarisasi (Z-Scale Standardization)</h6>
                                        <p>Sebelum mengukur jarak, sistem mengubah skala semua data yang berbeda (seperti rentang 0-100 dan rentang 1-5) menjadi skala yang seragam menggunakan rumus matematis <em>Z-Score</em>. Tujuannya agar tidak ada parameter besar yang mendominasi dan mengabaikan parameter kecil.</p>
                                        <p class="font-weight-bold text-dark"><code>Z = (Nilai_Asli - Rata_rata_Semua_Siswa) / Standar_Deviasi</code></p>
                                        <hr>
                                        <h6 class="font-weight-bold text-primary">2. Menghitung Jarak Euclidean 10 Dimensi</h6>
                                        <p>Sistem mencari kecocokan dengan mengukur seberapa "jauh" jarak matematis seorang siswa dengan Titik Pusat (Centroid) dari setiap Kluster. Jarak ini diukur sekaligus pada ke-10 dimensi profil yang telah distandarisasi tadi, secara prinsip mirip seperti Teorema Pythagoras namun di ruang 10 Dimensi.</p>
                                        <p class="font-weight-bold text-dark"><code>Jarak = &radic;[ (Z1 - C1)&sup2; + (Z2 - C2)&sup2; + ... + (Z10 - C10)&sup2; ]</code></p>
                                        <hr>
                                        <h6 class="font-weight-bold text-primary">3. Keputusan Pengelompokan (Clustering)</h6>
                                        <p>Setelah jarak ke semua titik pusat kluster diukur secara objektif, sistem akan mendaftarkan siswa tersebut ke kluster yang nilai jaraknya <strong>paling kecil (paling mirip)</strong>. Anda bisa melihat bukti persaingan jarak tersebut secara transparan pada kolom berwarna kuning di ujung <em>Tabel Data Mentah</em> di atas (angka bercetak hijau tebal adalah jarak terdekat yang dipilih sistem).</p>
                                    </div>
                                </div>
                            </div>
                            <!-- End Theory Accordion -->

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

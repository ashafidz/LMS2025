@extends('layouts.app-layout')
@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Hasil Polling</h5>
                        <p class="m-b-0">{{ $polling->question }}</p>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('instructor.modules.lessons.index', $lesson->module) }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Kembali ke Modul
                    </a>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.courses.modules.index', $lesson->module->course) }}">Modul Saya</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $lesson->module) }}">{{ Str::limit($lesson->module->title, 20) }}</a></li>
                        <li class="breadcrumb-item"><a href="#!">Hasil Polling</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="pcoded-inner-content">
        <div class="main-body"><div class="page-wrapper"><div class="page-body">
            <div class="row">
                <!-- Data Detail Polling -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Detail Polling</h5>
                        </div>
                        <div class="card-block">
                            <h6 class="mb-3">{{ $polling->question }}</h6>
                            <p class="text-muted">{{ $polling->description }}</p>
                            
                            <hr>
                            <p class="d-flex align-items-center"><strong>Status:</strong> 
                                @if($polling->is_active)
                                    <span class="badge badge-success ml-2">Aktif</span>
                                @else
                                    <span class="badge badge-danger ml-2">Ditutup</span>
                                @endif

                                <form action="{{ route('instructor.lessons.polling.toggle_status', $lesson) }}" method="POST" class="ml-auto">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $polling->is_active ? 'btn-danger' : 'btn-success' }}">
                                        {{ $polling->is_active ? 'Nonaktifkan Polling' : 'Aktifkan Polling' }}
                                    </button>
                                </form>
                            </p>
                            <p><strong>Total Responden:</strong> {{ $totalVoters }} 
                                <span class="text-muted small">({{ $totalResponses }} total pilihan)</span>
                            </p>
                            
                            <h6 class="mt-4">Rincian Jawaban:</h6>
                            <ul class="list-group">
                                @foreach($options as $option)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $option->text }}
                                    <span class="badge badge-primary badge-pill">{{ $option->responses_count }} ({{ $totalVoters > 0 ? round(($option->responses_count / $totalVoters) * 100) : 0 }}%)</span>
                                </li>
                                @if($option->responses_count > 0)
                                <div class="px-3 py-2 mb-2 bg-light text-muted small border-left border-right border-bottom d-flex justify-content-between align-items-center" style="margin-top: -1px;">
                                    <span>Terdapat <strong>{{ $option->responses_count }}</strong> Responden</span>
                                    <button class="btn btn-xs btn-outline-info" data-toggle="modal" data-target="#modalOption{{ $option->id }}">Lihat Detail</button>
                                </div>
                                @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Visualisasi -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Visualisasi Hasil</h5>
                        </div>
                        <div class="card-block text-center">
                            @if($totalResponses > 0)
                                <canvas id="pollingChart" height="250"></canvas>
                            @else
                                <div class="alert alert-info">Belum ada responden yang mengisi polling ini.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div></div></div>
    </div>
</div>

<!-- Modals dipindahkan ke root body agar tidak trapped di dalam card/overflow -->
@foreach($options as $option)
    @if($option->responses_count > 0)
    <div class="modal fade" id="modalOption{{ $option->id }}" tabindex="-1" role="dialog" aria-labelledby="modalOption{{ $option->id }}Label" aria-hidden="true" style="z-index: 1050;">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalOption{{ $option->id }}Label">Detail Responden: {{ $option->text }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" style="max-height: 400px; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" width="50">No</th>
                                    <th>NRP / NIM</th>
                                    <th>Nama Lengkap</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($option->responses as $idx => $response)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td>{{ $response->user->studentProfile->unique_id_number ?? '-' }}</td>
                                    <td>
                                        {{ $response->user->name ?? 'Anonim' }}
                                        <div class="text-muted small">{{ $response->user->email ?? '' }}</div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection

@push('scripts')
@if($totalResponses > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('pollingChart').getContext('2d');
        const labels = {!! json_encode($chartLabels) !!};
        const data = {!! json_encode($chartData) !!};

        const backgroundColors = [
            'rgba(255, 99, 132, 0.7)',
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)'
        ];

        new Chart(ctx, {
            type: 'pie', // Bisa diubah ke 'bar' jika diinginkan diagram batang
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Suara',
                    data: data,
                    backgroundColor: backgroundColors.slice(0, data.length),
                    borderColor: backgroundColors.map(color => color.replace('0.7', '1')).slice(0, data.length),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    });
</script>
@endif
@endpush

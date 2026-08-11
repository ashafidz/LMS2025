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
                            <p><strong>Total Responden:</strong> {{ $totalResponses }}</p>
                            
                            <h6 class="mt-4">Rincian Jawaban:</h6>
                            <ul class="list-group">
                                @foreach($options as $option)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $option->text }}
                                    <span class="badge badge-primary badge-pill">{{ $option->responses_count }} ({{ $totalResponses > 0 ? round(($option->responses_count / $totalResponses) * 100) : 0 }}%)</span>
                                </li>
                                @if($option->responses_count > 0)
                                <div class="px-3 py-2 mb-2 bg-light text-muted small border-left border-right border-bottom" style="margin-top: -1px;">
                                    <strong>Pemilih:</strong> {{ implode(', ', $option->responses->map(function($r) { return $r->user->name ?? 'User'; })->toArray()) }}
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

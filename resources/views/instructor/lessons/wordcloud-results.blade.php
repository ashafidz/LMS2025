@extends('layouts.app-layout')
@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Hasil Word Cloud</h5>
                        <p class="m-b-0">{{ $wordcloud->question }}</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.courses.modules.index', $lesson->module->course) }}">Modul Saya</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $lesson->module) }}">Pelajaran</a></li>
                        <li class="breadcrumb-item">Hasil Word Cloud</li>
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
                        <!-- Data Detail Word Cloud -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Detail Word Cloud</h5>
                                </div>
                                <div class="card-block">
                                    <h6 class="mb-3">{{ $wordcloud->question }}</h6>
                                    <p class="text-muted">{{ $wordcloud->description }}</p>
                                    
                                    <hr>
                                    <p><strong>Status:</strong> 
                                        @if($wordcloud->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-danger">Ditutup</span>
                                        @endif
                                    </p>
                                    <p><strong>Total Responden:</strong> {{ $totalResponses }}</p>
                                    
                                    <h6 class="mt-4">Daftar Kata (Frekuensi):</h6>
                                    <ul class="list-group" style="max-height: 300px; overflow-y: auto;">
                                        @foreach($wordCounts as $word => $count)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            {{ $word }}
                                            <span class="badge badge-primary badge-pill">{{ $count }}x</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Visualisasi -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Visualisasi Word Cloud</h5>
                                </div>
                                <div class="card-block text-center" style="min-height: 400px; position: relative;">
                                    @if($totalResponses > 0)
                                        <canvas id="wordCloudCanvas" width="800" height="400" style="width: 100%; height: auto;"></canvas>
                                    @else
                                        <div class="alert alert-info mt-5">Belum ada kata yang dikirimkan oleh siswa.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <a href="{{ route('instructor.modules.lessons.index', $lesson->module) }}" class="btn btn-secondary">Kembali ke Modul</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($totalResponses > 0)
<script src="https://cdnjs.cloudflare.com/ajax/libs/wordcloud2.js/1.2.2/wordcloud2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const wordList = {!! json_encode($wordCloudList) !!}; // format: [['word', 10], ['word2', 5]]
        
        const canvas = document.getElementById('wordCloudCanvas');
        
        // Define color palette matching rich aesthetics
        const colors = ['#007bff', '#28a745', '#17a2b8', '#ffc107', '#fd7e14', '#e83e8c', '#6f42c1'];
        
        WordCloud(canvas, {
            list: wordList,
            gridSize: Math.round(16 * document.getElementById('wordCloudCanvas').offsetWidth / 1024),
            weightFactor: function (size) {
                // Skala font agar yang terbesar terlihat bagus tapi yang kecil juga terbaca
                return Math.pow(size, 0.8) * 20; 
            },
            fontFamily: 'Inter, Roboto, sans-serif',
            color: function (word, weight) {
                return colors[Math.floor(Math.random() * colors.length)];
            },
            rotateRatio: 0.2, // Sedikit kemiringan agar estetik
            rotationSteps: 2,
            backgroundColor: '#ffffff',
            shape: 'circle',
            hover: window.drawBox,
        });
    });
</script>
@endif
@endpush

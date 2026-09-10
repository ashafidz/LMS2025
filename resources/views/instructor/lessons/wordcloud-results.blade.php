@extends('layouts.app-layout')
@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Hasil Word Cloud</h5>
                        <p class="m-b-0">{{ $wordcloud->question }}</p>
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
                                    <p class="d-flex align-items-center"><strong>Status:</strong> 
                                        @if($wordcloud->is_active)
                                            <span class="badge badge-success ml-2">Aktif</span>
                                        @else
                                            <span class="badge badge-danger ml-2">Ditutup</span>
                                        @endif

                                        <form action="{{ route('instructor.lessons.wordcloud.toggle_status', $lesson) }}" method="POST" class="ml-auto">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $wordcloud->is_active ? 'btn-danger' : 'btn-success' }}">
                                                {{ $wordcloud->is_active ? 'Nonaktifkan Word Cloud' : 'Aktifkan Word Cloud' }}
                                            </button>
                                        </form>
                                    </p>
                                    <p><strong>Total Responden:</strong> {{ $totalResponses }}</p>
                                    
                                    <h6 class="mt-4">Daftar Kata (Frekuensi):</h6>
                                    <ul class="list-group" style="max-height: 400px; overflow-y: auto;">
                                        @foreach($wordCounts as $word => $count)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $word }}</strong>
                                            </div>
                                            <div>
                                                <span class="badge badge-primary badge-pill mr-2">{{ $count }}x</span>
                                                <button class="btn btn-xs btn-outline-info" data-toggle="modal" data-target="#modalWord{{ md5($word) }}" title="Lihat Responden"><i class="fa fa-users"></i></button>
                                            </div>
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

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals dipindahkan ke root body agar tidak trapped di dalam card/overflow -->
@foreach($wordCounts as $word => $count)
<div class="modal fade" id="modalWord{{ md5($word) }}" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1050;">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Responden untuk kata: <strong>"{{ $word }}"</strong></h5>
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
                            @if(isset($responsesGrouped[$word]))
                                @foreach($responsesGrouped[$word] as $idx => $response)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td>{{ $response->user->studentProfile->unique_id_number ?? '-' }}</td>
                                    <td>
                                        {{ $response->user->name ?? 'Anonim' }}
                                        <div class="text-muted small">{{ $response->user->email ?? '' }}</div>
                                    </td>
                                </tr>
                                @endforeach
                            @endif
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
@endforeach

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
        
        let maxCount = 1;
        if (wordList.length > 0) {
            maxCount = Math.max(...wordList.map(item => item[1]));
        }

        WordCloud(canvas, {
            list: wordList,
            gridSize: Math.round(16 * document.getElementById('wordCloudCanvas').offsetWidth / 1024),
            weightFactor: function (size) {
                // Normalisasi proporsional berdasarkan kata terbanyak
                const minSize = 20;
                const maxSize = 120; // Max font size agar tetap muat di canvas
                if (maxCount <= 1) return minSize;
                return minSize + ((size - 1) / (maxCount - 1)) * (maxSize - minSize);
            },
            shrinkToFit: true, // Pastikan jika max font masih kepanjangan, canvas akan otomatis mengecilkannya
            drawOutOfBound: false,
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

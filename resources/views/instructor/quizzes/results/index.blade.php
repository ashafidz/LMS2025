@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Hasil Kuis</h5>
                        <p class="m-b-0">Kuis: <strong>{{ $quiz->title }}</strong></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $quiz->lesson->module) }}">Daftar Pelajaran</a></li>
                        <li class="breadcrumb-item"><a href="#!">Hasil Kuis</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Page-header end -->

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Riwayat Pengerjaan Siswa</h5>
                                </div>
                                <div class="card-block table-border-style">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">NIM/NIP/NIDN</th>
                                                    <th>Nama Siswa</th>
                                                    <th>Waktu Selesai</th>
                                                    <th>Skor</th>
                                                    <th>Status</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($attempts as $attempt)
                                                    <tr>
                                                        <td class="text-center">{{ $attempt->student->unique_id_number ?? '-' }}</td>
                                                        <td>{{ $attempt->student->name }}</td>
                                                        <td>{{ $attempt->end_time ? $attempt->end_time->format('d M Y, H:i') : 'Dalam Pengerjaan' }}</td>
                                                        <td><strong>{{ rtrim(rtrim(number_format($attempt->score, 2, ',', '.'), '0'), ',') }}</strong></td>
                                                        <td>
                                                            @if($attempt->status == 'passed')
                                                                <label class="label label-success">Lulus</label>
                                                            @elseif($attempt->status == 'failed')
                                                                <label class="label label-danger">Gagal</label>
                                                            @else
                                                                <label class="label label-warning">In Progress</label>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="{{ route('instructor.quiz.review_attempt', $attempt->id) }}" class="btn btn-primary btn-sm">
                                                                Periksa Jawaban
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center">Belum ada siswa yang mengerjakan kuis ini.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        {{ $attempts->links() }}
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
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
                        <p class="m-b-0">Judul Kuis: <strong>{{ $quiz->title }}</strong></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="#!"><i class="fa fa-home"></i> </a></li>
                        <li class="breadcrumb-item"><a href="#!">Kursus Saya</a></li>
                        <li class="breadcrumb-item"><a href="#!">Modul Saya</a></li>
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
                                    <span>Tabel ini menampilkan status pengerjaan kuis untuk semua siswa yang terdaftar di kursus ini.</span>
                                </div>
                                <div class="card-block table-border-style">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">NIM/NIP/NIDN</th>
                                                    <th>Nama Siswa</th>
                                                    <th>Status Pengerjaan</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($enrolledStudents as $student)
                                                    <tr>
                                                        <td class="text-center">{{ $student->studentProfile->unique_id_number ? $student->studentProfile->unique_id_number : '-' }}</td>
                                                        <td>{{ $student->name }}</td>
                                                        <td>
                                                            @php
                                                                $statusClass = '';
                                                                if ($student->quiz_status === 'Lulus') $statusClass = 'label-success';
                                                                elseif ($student->quiz_status === 'Gagal') $statusClass = 'label-danger';
                                                                else $statusClass = 'label-default';
                                                            @endphp
                                                            <label class="label {{ $statusClass }}">{{ $student->quiz_status }}</label>
                                                        </td>
                                                        <td class="text-center">
                                                            @if($student->attempts->isNotEmpty())
                                                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#historyModal-{{ $student->id }}">
                                                                    Lihat Riwayat
                                                                </button>
                                                            @else
                                                                <button class="btn btn-secondary btn-sm" disabled>Lihat Riwayat</button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center">Belum ada siswa yang terdaftar di kursus ini.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        {{ $enrolledStudents->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Riwayat Pengerjaan untuk setiap siswa -->
    @foreach ($enrolledStudents as $student)
        @if($student->attempts->isNotEmpty())
        <div class="modal fade" id="historyModal-{{ $student->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Riwayat Pengerjaan: {{ $student->name }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Waktu Selesai</th>
                                        <th>Skor</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($student->attempts->sortByDesc('created_at') as $attempt)
                                    <tr>
                                        <td>{{ $attempt->end_time ? $attempt->end_time->format('d M Y, H:i') : 'Dalam Pengerjaan' }}</td>
                                        <td><strong>{{ rtrim(rtrim(number_format($attempt->score, 2, ',', '.'), '0'), ',') }}</strong></td>
                                        <td>
                                            @if($attempt->status == 'passed')
                                                <label class="label label-success">Lulus</label>
                                            @else
                                                <label class="label label-danger">Gagal</label>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('instructor.quiz.review_attempt', $attempt->id) }}" class="btn btn-inverse btn-sm">Periksa Jawaban</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endforeach
</div>
@endsection
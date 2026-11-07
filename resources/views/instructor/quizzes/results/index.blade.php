@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Kursus Saya', route('instructor.courses.index')], [\Illuminate\Support\Str::limit($quiz->lesson->module->course->title, 30), route('instructor.courses.modules.index', $quiz->lesson->module->course)], ['Daftar Pelajaran', route('instructor.modules.lessons.index', $quiz->lesson->module)], ['Hasil Kuis', null]]">
        <x-slot:title>Hasil Kuis</x-slot:title>
        <x-slot:subtitle>Judul Kuis: <strong>{{ $quiz->title }}</strong></x-slot:subtitle>
    </x-dash.page-head>
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
                                                        <td class="text-center">{{ $student->studentProfile?->unique_id_number ? $student->studentProfile?->unique_id_number : '-' }}</td>
                                                        <td><a href="{{ route('profile.show', $student->id) }}">{{ $student->name }}</a></td>
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
                                    {{-- <div class="d-flex justify-content-center">
                                        {{ $enrolledStudents->links() }}
                                    </div> --}}
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
                                        <th>Nilai</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($student->attempts->sortByDesc('created_at') as $attempt)
                                    @php
                                        // Gunakan scaled_score dari DB, fallback ke kalkulasi manual untuk data lama
                                        $studentScoreScaled = $attempt->scaled_score
                                            ?? (($totalMaxScore > 0) ? min(100, round(($attempt->score / $totalMaxScore) * 100, 2)) : 0);
                                    @endphp
                                    <tr>
                                        <td>{{ $attempt->end_time ? $attempt->end_time->format('d M Y, H:i') : 'Dalam Pengerjaan' }}</td>
                                        <td>
                                            <strong>{{ rtrim(rtrim(number_format($attempt->score, 2, ',', '.'), '0'), ',') }}</strong>
                                            <br>
                                            <small class="text-muted">Min: {{ rtrim(rtrim(number_format($minimumScore, 2, ',', '.'), '0'), ',') }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ rtrim(rtrim(number_format($studentScoreScaled, 2, ',', '.'), '0'), ',') }}</strong>
                                            <br>
                                            <small class="text-muted">Min: {{ rtrim(rtrim(number_format($minimumScoreScaled, 2, ',', '.'), '0'), ',') }}</small>
                                        </td>
                                        <td>
                                            @if($attempt->status == 'passed')
                                                <label class="label label-success">Lulus</label>
                                            @else
                                                <label class="label label-danger">Gagal</label>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('instructor.quiz.review_attempt', $attempt) }}" class="btn btn-secondary btn-sm">Periksa Jawaban</a>
                                            <a href="{{ route('instructor.quiz.monitoring.detail', $attempt) }}" class="btn btn-secondary btn-sm" title="Lihat Detail Monitoring">
                                                <i class="fa fa-eye"></i> Detail Pelanggaran
                                            </a>
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
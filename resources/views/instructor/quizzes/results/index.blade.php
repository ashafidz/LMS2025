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
                                    <x-dash.list-toolbar placeholder="Cari nama, email, atau NIM/NIP/NIDN..." label="Cari siswa" />
                                    @php
                                        $tz = auth()->user()->timezone ?: session('user_timezone', config('app.timezone'));
                                        $localDay = fn ($date) => $date->copy()->setTimezone($tz)->locale('id')->isoFormat('D MMM YYYY');
                                        $localClock = fn ($date) => $date->copy()->setTimezone($tz)->isoFormat('HH.mm');
                                    @endphp
                                    <div class="mb-3">
                                        <span class="rs-chip mr-2">Nilai minimum lulus: <strong>{{ rtrim(rtrim(number_format($minimumScoreScaled, 2, ',', '.'), '0'), ',') }}</strong></span>
                                        <span class="rs-chip">Nilai maksimal: <strong>100</strong></span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">No</th>
                                                    @include('instructor.partials._sort_th', ['key' => 'nim', 'label' => 'NIM/NIP/NIDN', 'class' => 'text-center'])
                                                    @include('instructor.partials._sort_th', ['key' => 'nama', 'label' => 'Nama Siswa'])
                                                    @include('instructor.partials._sort_th', ['key' => 'nilai', 'label' => 'Nilai', 'class' => 'text-center', 'first' => 'desc'])
                                                    @include('instructor.partials._sort_th', ['key' => 'selesai', 'label' => 'Waktu Pengerjaan'])
                                                    <th>Status</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($enrolledStudents as $student)
                                                    <tr>
                                                        <td class="text-center">{{ $enrolledStudents->firstItem() + $loop->index }}</td>
                                                        <td class="text-center">{{ $student->studentProfile?->unique_id_number ? $student->studentProfile?->unique_id_number : '-' }}</td>
                                                        <td><a href="{{ route('profile.show', $student->id) }}">{{ $student->name }}</a></td>
                                                        <td class="text-center">
                                                            @if ($student->best_score !== null)
                                                                @php $bestScoreText = rtrim(rtrim(number_format($student->best_score, 2, ',', '.'), '0'), ','); @endphp
                                                                @if ($student->quiz_status === 'Lulus')
                                                                    <strong>{{ $bestScoreText }}</strong>
                                                                @else
                                                                    {{ $bestScoreText }}
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        @php
                                                            $best = $student->best_attempt;
                                                            $minutes = ($best && $best->start_time && $best->end_time)
                                                                ? intdiv(abs($best->end_time->getTimestamp() - $best->start_time->getTimestamp()), 60)
                                                                : null;
                                                        @endphp
                                                        <td>
                                                            @if ($minutes === null)
                                                                -
                                                            @else
                                                                {{-- Mulai - selesai (jam lokal) dan lama pengerjaan, dari percobaan yang sama dengan Nilai. --}}
                                                                <div class="rs-attempt-time">
                                                                    <span>{{ $localDay($best->start_time) }}</span>
                                                                    <span>
                                                                        {{ $localClock($best->start_time) }} - @if ($localDay($best->end_time) !== $localDay($best->start_time)){{ $localDay($best->end_time) }}, @endif{{ $localClock($best->end_time) }}
                                                                    </span>
                                                                    <small class="text-muted">{{ $minutes < 1 ? 'Kurang dari 1 menit' : $minutes.' menit' }}</small>
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @php
                                                                $statusClass = '';
                                                                if ($student->quiz_status === 'Lulus') $statusClass = 'label-success';
                                                                elseif ($student->quiz_status === 'Gagal') $statusClass = 'label-danger';
                                                                elseif ($student->quiz_status === 'Sedang Mengerjakan') $statusClass = 'label-warning';
                                                                else $statusClass = 'label-default';
                                                            @endphp
                                                            <label class="label {{ $statusClass }}">{{ $student->quiz_status }}</label>
                                                        </td>
                                                        <td class="text-center">
                                                            @if($student->attempts->isNotEmpty())
                                                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#historyModal-{{ $student->id }}" title="Lihat riwayat pengerjaan {{ $student->name }}">
                                                                    Riwayat
                                                                </button>
                                                            @else
                                                                <button class="btn btn-secondary btn-sm" disabled>Riwayat</button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center">
                                                            @if (request()->filled('search'))
                                                                Tidak ada siswa yang cocok dengan "{{ request('search') }}".
                                                            @else
                                                                Belum ada siswa yang terdaftar di kursus ini.
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <x-dash.pagination :items="$enrolledStudents" />
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
                                        // Nilai 0-100 dengan aturan yang sama seperti tabel utama: nilai revisi diutamakan.
                                        $inProgress = $attempt->status === 'in_progress';
                                        $studentScoreScaled = $inProgress ? null : $attempt->finalScaledScore((float) $totalMaxScore);
                                        $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',');
                                    @endphp
                                    <tr>
                                        <td>{{ $attempt->end_time ? $localDay($attempt->end_time).', '.$localClock($attempt->end_time) : 'Sedang dikerjakan' }}</td>
                                        <td>
                                            @if ($inProgress || $attempt->score === null)
                                                -
                                            @else
                                                <strong>{{ $fmt($attempt->revised_score ?? $attempt->score) }}</strong>
                                                @if ($attempt->revised_score !== null)
                                                    <small class="text-muted d-block">Direvisi (awal {{ $fmt($attempt->score) }})</small>
                                                @endif
                                            @endif
                                            <small class="text-muted d-block">Min: {{ $fmt($minimumScore) }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $studentScoreScaled === null ? '-' : $fmt($studentScoreScaled) }}</strong>
                                            <small class="text-muted d-block">Min: {{ $fmt($minimumScoreScaled) }}</small>
                                        </td>
                                        <td>
                                            @if ($attempt->status === 'passed')
                                                <label class="label label-success">Lulus</label>
                                            @elseif ($inProgress)
                                                <label class="label label-warning">Sedang Mengerjakan</label>
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
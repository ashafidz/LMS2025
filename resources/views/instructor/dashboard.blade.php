@extends('layouts.app-layout')

@section('title', 'Beranda')

@section('content')
@php
    $user = Auth::user();
    $tz = $user->timezone ?: session('user_timezone', config('app.timezone'));
    $today = now($tz)->locale('id')->isoFormat('dddd, D MMMM YYYY');
    $firstName = \Illuminate\Support\Str::of($user->name)->trim()->before(' ')->toString();
    $local = fn ($date) => $date->copy()->setTimezone($tz)->locale('id')->isoFormat('D MMM, HH.mm');
    $statusPill = ['published' => 'rs-pill--green', 'pending_review' => 'rs-pill--amber', 'rejected' => 'rs-pill--red', 'draft' => 'rs-pill--gray', 'private' => 'rs-pill--gray'];
    $stateRows = [
        'pending_review' => fn ($n) => $n.' kursus menunggu tinjauan',
        'rejected' => fn ($n) => $n.' kursus ditolak, perlu diperbaiki',
        'draft' => fn ($n) => $n.' draf',
    ];
    $hasTodo = $toGrade->isNotEmpty() || $courseStates->isNotEmpty();
@endphp
<div class="pcoded-content">
    <x-dash.page-head>
        <x-slot:title>Halo, {{ $firstName }}!</x-slot:title>
        <x-slot:subtitle>{{ $today }}</x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Buat kursus baru</a>
            <a href="{{ route('instructor.question-bank.topics.index') }}" class="btn btn-secondary"><i class="bi bi-question-circle" aria-hidden="true"></i> Bank soal</a>
        </x-slot:actions>
    </x-dash.page-head>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="rs-home__stats rs-home__stats--4">
                        <a class="rs-stat" href="{{ route('instructor.courses.index') }}">
                            <span class="rs-tile" aria-hidden="true"><i class="fa fa-book"></i></span>
                            <div><strong>{{ $totalCourses }}</strong><span>Total kursus</span></div>
                        </a>
                        <div class="rs-stat">
                            <span class="rs-tile" aria-hidden="true"><i class="fa fa-users"></i></span>
                            <div><strong>{{ $totalStudents }}</strong><span>Total siswa</span></div>
                        </div>
                        <div class="rs-stat">
                            <span class="rs-tile" aria-hidden="true"><i class="fa fa-graduation-cap"></i></span>
                            <div><strong>{{ $totalCompletedStudents }}</strong><span>Siswa selesai</span></div>
                        </div>
                        <div class="rs-stat">
                            <span class="rs-tile" aria-hidden="true"><i class="fa fa-hourglass-half"></i></span>
                            <div><strong>{{ $totalInProgressStudents }}</strong><span>Sedang belajar</span></div>
                        </div>
                    </div>

                    <div class="rs-home__grid">
                        <div class="rs-home__col">
                            <section class="card" aria-labelledby="todo-title">
                                <div class="card-header"><h2 id="todo-title" class="h5 mb-0">Perlu ditindaklanjuti</h2></div>
                                <div class="card-block">
                                    @if ($hasTodo)
                                        @foreach ($toGrade as $row)
                                            <div class="rs-todo">
                                                <span class="rs-todo__ic" aria-hidden="true"><i class="bi bi-pencil-square"></i></span>
                                                <div class="rs-todo__text">
                                                    <b>{{ $row->submissions_count }} tugas menunggu dinilai: {{ $row->lesson_title }}</b>
                                                    <small>{{ $row->course_title }}</small>
                                                </div>
                                                <a href="{{ $row->url }}" class="btn btn-primary btn-sm">Nilai<span class="sr-only">: {{ $row->lesson_title }}</span></a>
                                            </div>
                                        @endforeach
                                        @foreach ($stateRows as $status => $text)
                                            @if (($courseStates[$status] ?? 0) > 0)
                                                <div class="rs-todo">
                                                    <span class="rs-todo__ic" aria-hidden="true"><i class="bi {{ $status === 'rejected' ? 'bi-exclamation-circle' : ($status === 'pending_review' ? 'bi-hourglass-split' : 'bi-file-earmark') }}"></i></span>
                                                    <div class="rs-todo__text"><b>{{ $text($courseStates[$status]) }}</b></div>
                                                    <a href="{{ route('instructor.courses.index') }}" class="btn btn-secondary btn-sm">Lihat<span class="sr-only">: {{ $text($courseStates[$status]) }}</span></a>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <p class="rs-todo-empty mb-0"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Tidak ada yang perlu ditindaklanjuti.</p>
                                    @endif
                                </div>
                            </section>

                            <section class="card" aria-labelledby="mine-title">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h2 id="mine-title" class="h5 mb-0">Kursus saya</h2>
                                    <a href="{{ route('instructor.courses.index') }}">Lihat semua kursus</a>
                                </div>
                                <div class="card-block">
                                    @forelse ($courses as $course)
                                        @php
                                            $pct = $course->students_count > 0 ? (int) floor($course->completed_students_count / $course->students_count * 100) : null;
                                        @endphp
                                        <div class="rs-crs" @if ($loop->index >= 6) data-crs-extra hidden @endif>
                                            <span class="rs-tile" @if ($course->thumbnail_url) style="background-image: url('{{ Storage::url($course->thumbnail_url) }}'); background-size: cover" @endif aria-hidden="true">
                                                @unless ($course->thumbnail_url)<i class="fa fa-book"></i>@endunless
                                            </span>
                                            <div class="rs-crs__main">
                                                <div class="rs-crs__title">
                                                    <strong>{{ $course->title }}</strong>
                                                    <span class="rs-pill {{ $statusPill[$course->status] ?? 'rs-pill--gray' }}">{{ $course->status_label }}</span>
                                                </div>
                                                <small>
                                                    @if ($course->students_count > 0){{ $course->students_count }} siswa - @endif{{ $course->lessons_count }} materi
                                                </small>
                                                @if ($pct === null)
                                                    <small class="rs-crs__none">Belum ada siswa</small>
                                                @else
                                                    <div class="rs-crs__bar">
                                                        <div class="progress" role="progressbar" aria-label="Siswa yang selesai di {{ $course->title }}" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                                            <div class="progress-bar" style="width: {{ $pct }}%"></div>
                                                        </div>
                                                        <small>{{ $course->completed_students_count }} selesai, {{ $course->inprogress_students_count }} belum ({{ $pct }}%)</small>
                                                    </div>
                                                @endif
                                            </div>
                                            <a href="{{ route('instructor.courses.modules.index', $course) }}" class="btn btn-secondary btn-sm">Kelola<span class="sr-only">: {{ $course->title }}</span></a>
                                        </div>
                                    @empty
                                        <p class="mb-0">Anda belum membuat kursus.</p>
                                    @endforelse
                                    @if ($courses->count() > 6)
                                        {{-- Every course is rendered (no number is lost); rows after the 6th open in place. --}}
                                        <p class="rs-crs__more mb-0">
                                            <button type="button" class="btn btn-secondary btn-sm" id="crs-more" aria-expanded="false">Tampilkan semua ({{ $courses->count() }})</button>
                                        </p>
                                        <script>
                                            document.getElementById('crs-more').addEventListener('click', function () {
                                                var open = this.getAttribute('aria-expanded') !== 'true';
                                                document.querySelectorAll('[data-crs-extra]').forEach(function (row) { row.hidden = !open; });
                                                this.setAttribute('aria-expanded', String(open));
                                                this.textContent = open ? 'Tampilkan lebih sedikit' : 'Tampilkan semua ({{ $courses->count() }})';
                                            });
                                        </script>
                                        <noscript><style>[data-crs-extra] { display: flex !important; } #crs-more { display: none; }</style></noscript>
                                    @endif
                                </div>
                            </section>
                        </div>

                        <aside class="rs-home__col">
                            <section class="card" aria-labelledby="disc-title">
                                    <div class="card-header"><h2 id="disc-title" class="h5 mb-0">Diskusi terbaru</h2></div>
                                    <div class="card-block">
                                        @forelse ($recentDiscussions as $d)
                                            @php $module = $d->lesson?->module; @endphp
                                            <a class="rs-act rs-act--link" href="{{ $module ? route('instructor.modules.lessons.index', \App\Services\HashIdService::encode($module->id)) : route('instructor.courses.index') }}">
                                                <span class="rs-act__dot rs-act__dot--chat" aria-hidden="true"><i class="bi bi-chat-dots"></i></span>
                                                <div>
                                                    <b>{{ $d->user?->name ?? 'Siswa' }}</b>
                                                    <span class="rs-act__quote">{{ \Illuminate\Support\Str::limit(trim(html_entity_decode(strip_tags((string) $d->content))), 80) }}</span>
                                                    <small>{{ $d->lesson?->title }}{{ $module?->course ? ' ('.$module->course->title.')' : '' }} - {{ $local($d->created_at) }}</small>
                                                </div>
                                            </a>
                                        @empty
                                            <p class="rs-side-empty"><i class="bi bi-chat-dots" aria-hidden="true"></i> Belum ada diskusi baru dari siswa. Komentar siswa di halaman pelajaran akan muncul di sini.</p>
                                        @endforelse
                                    </div>
                                </section>

                                <section class="card" aria-labelledby="rev-title">
                                    <div class="card-header"><h2 id="rev-title" class="h5 mb-0">Ulasan terbaru</h2></div>
                                    <div class="card-block">
                                        @forelse ($recentReviews as $r)
                                            <div class="rs-act">
                                                <div>
                                                    <span class="rs-stars" role="img" aria-label="Nilai {{ (int) $r->rating }} dari 5">
                                                        @for ($i = 1; $i <= 5; $i++)<i class="bi {{ $i <= (int) $r->rating ? 'bi-star-fill' : 'bi-star' }}" aria-hidden="true"></i>@endfor
                                                    </span>
                                                    @if (filled($r->comment))
                                                        <span class="rs-act__quote">{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $r->comment)), 100) }}</span>
                                                    @endif
                                                    <small>{{ $r->user?->name ?? 'Siswa' }} - {{ $r->course?->title }}</small>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="rs-side-empty"><i class="bi bi-star" aria-hidden="true"></i> Belum ada ulasan. Ulasan siswa untuk kursus Anda akan muncul di sini.</p>
                                        @endforelse
                                    </div>
                                </section>
                        </aside>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app-layout')

@section('title', 'Beranda')

@section('content')
@php
    $tz = $user->timezone ?: session('user_timezone', config('app.timezone'));
    $today = now($tz)->locale('id')->isoFormat('dddd, D MMMM YYYY');
    $firstName = \Illuminate\Support\Str::of($user->name)->trim()->before(' ')->toString();
    $tones = ['blue', 'lav', 'amber'];
    $ringTones = ['', 'rs-ring--lav', 'rs-ring--amber'];
    $percent = fn ($c) => $c->lessons_count > 0 ? (int) floor($c->completed_lessons_count / $c->lessons_count * 100) : null;
    $number = fn ($v) => $v === null ? 'Belum ada data' : number_format($v, 0, ',', '.');
@endphp
<div class="rs-home">
    <header class="rs-home__head">
        <h1>Halo, {{ $firstName }}!</h1>
        <p>{{ $today }}</p>
    </header>

    <div class="rs-home__grid">
        <div class="rs-home__col">
            <section aria-labelledby="lanjut-title">
                <h2 id="lanjut-title" class="rs-home__title">Lanjutkan belajar</h2>

                @forelse ($continue as $course)
                    @php
                        $pct = $percent($course);
                        $next = $nextLessons[$course->id] ?? null;
                        $thumb = $course->thumbnail_url ? "background-image: url('".Storage::url($course->thumbnail_url)."')" : null;
                    @endphp
                    @if ($loop->first)
                        <article class="rs-hero">
                            <div class="rs-hero__thumb {{ $thumb ? 'has-img' : 'rs-cover--c'.(($course->category_id ?? 0) % 5) }}" @if ($thumb) style="{{ $thumb }}" @endif aria-hidden="true"></div>
                            <div class="rs-hero__body">
                                @if ($course->category)<span class="rs-pill rs-pill--on-dark">{{ $course->category->name }}</span>@endif
                                <h3>{{ $course->title }}</h3>
                                @if ($next)
                                    <div class="rs-hero__next">
                                        <i class="bi {{ $next['locked'] ? 'bi-lock-fill' : 'bi-play-circle-fill' }}" aria-hidden="true"></i>
                                        <div>
                                            <span>Materi berikutnya</span>
                                            @if ($next['locked'])
                                                <b>Modul berikutnya masih terkunci</b>
                                            @else
                                                <b>{{ $next['title'] }} ({{ $next['type'] }})</b>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                <div class="rs-hero__prog">
                                    <div class="progress" role="progressbar" aria-label="Progres {{ $course->title }}" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <b>{{ $course->completed_lessons_count }} dari {{ $course->lessons_count }} materi</b>
                                </div>
                                <a class="btn rs-hero__btn" href="{{ route('student.courses.show', $course->slug) }}">Lanjut belajar <i class="bi bi-arrow-right" aria-hidden="true"></i><span class="sr-only">: {{ $course->title }}</span></a>
                            </div>
                        </article>
                    @else
                        <article class="rs-course-card rs-course-card--{{ $tones[$loop->index % 3] }}">
                            <span class="rs-course-card__thumb" @if ($thumb) style="{{ $thumb }}" @endif aria-hidden="true"></span>
                            <div class="rs-course-card__body">
                                <h3>{{ $course->title }}</h3>
                                <p>{{ $course->completed_lessons_count }} dari {{ $course->lessons_count }} materi selesai</p>
                                <div class="progress" role="progressbar" aria-label="Progres {{ $course->title }}" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                            <div class="rs-course-card__stat">
                                <b>{{ $pct }}%</b>
                                <a href="{{ route('student.courses.show', $course->slug) }}">Lanjut<span class="sr-only">: {{ $course->title }}</span></a>
                            </div>
                        </article>
                    @endif
                @empty
                    <div class="card">
                        <div class="card-block text-center p-4">
                            <x-rs.illustration :name="$enrolledCount > 0 ? 'rs-08-semua-selesai' : 'rs-07-cari-kursus'" size="md" class="mb-3" />
                            @if ($enrolledCount > 0)
                                <h3 class="h5">Semua kursusmu sudah selesai</h3>
                                <p class="mb-3">Cari kursus lain untuk terus belajar.</p>
                            @else
                                <h3 class="h5">Belum ada kursus. Yuk mulai belajar.</h3>
                                <p class="mb-3">Pilih kursus yang kamu butuhkan di katalog.</p>
                            @endif
                            <a href="{{ route('courses') }}" class="btn btn-primary">Lihat katalog</a>
                        </div>
                    </div>
                @endforelse
            </section>

            <div class="card rs-home__mine">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h2 class="h5 mb-0">Kursus saya</h2>
                    <a href="{{ route('student.my_courses') }}">Lihat semua</a>
                </div>
                <div class="card-block">
                    @forelse ($myCourses as $course)
                        @php $pct = $percent($course); @endphp
                        <a class="rs-home__row" href="{{ route('student.courses.show', $course->slug) }}">
                            <span class="rs-tile" @if ($course->thumbnail_url) style="background-image: url('{{ Storage::url($course->thumbnail_url) }}'); background-size: cover" @endif aria-hidden="true">
                                @unless ($course->thumbnail_url)<i class="fa fa-book"></i>@endunless
                            </span>
                            <span class="rs-home__row-text">
                                <strong>{{ $course->title }}</strong>
                                <small>
                                    @if ($pct === null) Belum ada materi
                                    @else {{ $course->completed_lessons_count }} dari {{ $course->lessons_count }} materi selesai @endif
                                </small>
                            </span>
                            @if ($pct === null)
                                {{-- tanpa materi: tidak ada cincin --}}
                            @elseif ($pct >= 100)
                                <span class="badge badge-success">Selesai</span>
                            @else
                                <span class="rs-ring {{ $ringTones[$loop->index % 3] }}" style="--p: {{ $pct }}" role="img" aria-label="{{ $pct }} persen selesai"><span>{{ $pct }}%</span></span>
                            @endif
                        </a>
                    @empty
                        <p class="mb-0">Belum ada kursus yang kamu ikuti.</p>
                    @endforelse
                </div>
            </div>

            @if ($recommended->isNotEmpty())
                <section aria-labelledby="rec-title">
                    <div class="rs-home__sechead">
                        <h2 id="rec-title" class="rs-home__title mb-0">Kursus untuk kamu</h2>
                        <a href="{{ route('courses') }}">Lihat katalog</a>
                    </div>
                    <div class="rs-rec">
                        @foreach ($recommended as $rc)
                            <a class="rs-rcard" href="{{ route('courses.show', $rc->slug) }}">
                                <span class="rs-rcard__img {{ $rc->thumbnail_url ? 'has-img' : 'rs-cover--c'.(($rc->category_id ?? 0) % 5) }}" @if ($rc->thumbnail_url) style="background-image: url('{{ Storage::url($rc->thumbnail_url) }}')" @endif aria-hidden="true"></span>
                                <span class="rs-rcard__body">
                                    @if ($rc->category)<span class="rs-pill">{{ $rc->category->name }}</span>@endif
                                    <strong>{{ $rc->title }}</strong>
                                    @if ($rc->instructor)<small>{{ $rc->instructor->name }}</small>@endif
                                    <x-rs.price :course="$rc" />
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside class="rs-home__col" aria-labelledby="ringkasan-title">
            <section>
                <h2 id="ringkasan-title" class="rs-home__title">Ringkasan</h2>
                <div class="rs-home__stats">
                    <a class="rs-stat" href="{{ route('student.points.index') }}">
                        <span class="rs-tile" aria-hidden="true"><i class="fa fa-star"></i></span>
                        <div><strong @if ($points !== null) data-rs-countup="{{ (int) $points }}" @endif>{{ $number($points) }}</strong><span>Poin</span></div>
                    </a>
                    <a class="rs-stat" href="{{ route('student.diamonds.index') }}">
                        <span class="rs-tile" aria-hidden="true"><i class="fa fa-diamond"></i></span>
                        <div><strong @if ($diamonds !== null) data-rs-countup="{{ (int) $diamonds }}" @endif>{{ $number($diamonds) }}</strong><span>Diamond</span></div>
                    </a>
                    <a class="rs-stat" href="{{ route('student.certificates.index') }}">
                        <span class="rs-tile" aria-hidden="true"><i class="fa fa-certificate"></i></span>
                        <div><strong @if ($certificates !== null) data-rs-countup="{{ (int) $certificates }}" @endif>{{ $number($certificates) }}</strong><span>Sertifikat</span></div>
                    </a>
                </div>
            </section>

            @if ($nextBadge)
                @php
                    $nb = $nextBadge['badge'];
                    $nbPct = (int) floor($nextBadge['ratio'] * 100);
                @endphp
                <section class="card" aria-labelledby="badge-title">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h2 id="badge-title" class="h5 mb-0">Lencana berikutnya</h2>
                        <a href="{{ route('student.badges.index') }}">Semua lencana</a>
                    </div>
                    <div class="card-block">
                        <div class="rs-badge-next">
                            @if ($nb->icon_path)
                                <img class="rs-medal" src="{{ asset('storage/'.$nb->icon_path) }}" alt="">
                            @else
                                <span class="rs-medal" aria-hidden="true"><i class="bi bi-shield-check"></i></span>
                            @endif
                            <div class="rs-badge-next__text">
                                <strong>{{ $nb->title }}</strong>
                                @foreach ($nextBadge['rules'] as $rule)
                                    <span class="rs-badge-next__rule">{{ $rule['sentence'] ?? $rule['label'] }}</span>
                                @endforeach
                                <div class="progress" role="progressbar" aria-label="Progres lencana {{ $nb->title }}" aria-valuenow="{{ $nbPct }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: {{ $nbPct }}%"></div>
                                </div>
                                @if (count($nextBadge['rules']) === 1)
                                    <span class="rs-badge-next__count"><b>{{ $nextBadge['current'] }}</b> dari {{ $nextBadge['target'] }} {{ $nextBadge['rules'][0]['unit'] }}</span>
                                @else
                                    <span class="rs-badge-next__count"><b>{{ $nextBadge['current'] }}</b> dari {{ $nextBadge['target'] }} langkah</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            @else
                <section class="card" aria-labelledby="badge-title">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h2 id="badge-title" class="h5 mb-0">Lencana</h2>
                        <a href="{{ route('student.badges.index') }}">Semua lencana</a>
                    </div>
                    <div class="card-block">
                        @if ($badgesOwned > 0)
                            <p class="rs-side-empty"><i class="bi bi-trophy" aria-hidden="true"></i> Hebat! Semua lencana yang tersedia sudah kamu raih ({{ $badgesOwned }} lencana).</p>
                        @else
                            <p class="rs-side-empty"><i class="bi bi-shield-check" aria-hidden="true"></i> Belum ada lencana yang bisa dikumpulkan saat ini. Lencana baru akan muncul di sini.</p>
                        @endif
                    </div>
                </section>
            @endif

                <section class="card" aria-labelledby="act-title">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h2 id="act-title" class="h5 mb-0">Aktivitas poin</h2>
                        <a href="{{ route('student.points.index') }}">Riwayat lengkap</a>
                    </div>
                    <div class="card-block">
                        @forelse ($recentPoints as $ph)
                            <div class="rs-act">
                                <span class="rs-act__dot {{ $ph->points < 0 ? 'is-minus' : '' }}">{{ $ph->points > 0 ? '+' : '' }}{{ $ph->points }}</span>
                                <div>
                                    <b>{{ $ph->description ?: 'Poin dari kursus' }}</b>
                                    <small>{{ $ph->course?->title ? $ph->course->title.' - ' : '' }}{{ $ph->created_at->copy()->setTimezone($tz)->locale('id')->isoFormat('D MMM, HH.mm') }}</small>
                                </div>
                            </div>
                        @empty
                            <p class="rs-side-empty"><i class="bi bi-star" aria-hidden="true"></i> Belum ada aktivitas poin. Selesaikan materi untuk mengumpulkan poin.</p>
                        @endforelse
                    </div>
                </section>
        </aside>
    </div>
</div>
@endsection

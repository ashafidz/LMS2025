@extends('layouts.home-layout')

@section('title', $course->title)

@section('content')
<div class="rs-page">
<section class="rs-pagehead">
  <div class="rs-wrap">
    <div class="rs-course-head">
      <div>
        <p class="rs-crumbs"><a href="{{ route('home') }}">Beranda</a> / <a href="{{ route('courses') }}">Katalog</a> / {{ $course->category?->name }}</p>
        <h1 class="rs-h1">{{ $course->title }}</h1>

        {{-- BAGIAN BARU: Ringkasan Rating --}}
        <div class="rs-meta-line">
          @if($reviewCount > 0)
            <x-rs.stars :value="$averageRating" />
            <span>{{ number_format($averageRating, 1, ',', '.') }} ({{ $reviewCount }} ulasan)</span>
          @else
            <span>Belum ada ulasan</span>
          @endif
          <span>Instruktur: <a class="rs-link" href="{{ route('profile.show', $course->instructor->id) }}">{{ $course->instructor->name }}</a></span>
        </div>
      </div>

      <aside class="rs-buy">
        <div class="rs-cover rs-cover--c{{ $course->category_id % 5 }} {{ $course->thumbnail_url ? 'rs-cover--photo' : '' }}">
          @if ($course->thumbnail_url)
            <img src="{{ Storage::url($course->thumbnail_url) }}" alt="" loading="lazy">
            <span></span>
          @else
            <span class="rs-cover__publisher">{{ $siteSettings->site_name ?? '' }}</span>
          @endif
          <span class="rs-cover__series">{{ $course->category?->name ?? 'Kursus' }}</span>
        </div>

        {{-- LOGIKA BARU: Tampilkan harga sesuai tipe pembayaran --}}
        <x-rs.price :course="$course" />

        {{-- Logika Tombol Aksi Dinamis --}}
        @auth
          @if (session('active_role') === 'student')
            @if($is_enrolled)
              <a href="{{ route('student.courses.show', $course->slug) }}" class="rs-btn rs-btn--ok rs-btn--block">Lanjutkan Belajar</a>
            @else
              @if($course->payment_type === 'money')
                <form action="{{ route('student.cart.add', $course) }}" method="POST">
                  @csrf
                  <button type="submit" class="rs-btn rs-btn--block">Tambahkan ke keranjang</button>
                </form>
              @elseif($course->payment_type === 'diamonds')
                <form action="{{ route('student.courses.purchase_with_diamonds', $course) }}" method="POST" onsubmit="return confirm('Beli kursus ini dengan {{ $course->diamond_price }} diamond?');">
                  @csrf
                  <button type="submit" class="rs-btn rs-btn--block" {{ Auth::user()->diamond_balance < $course->diamond_price ? 'disabled' : '' }}>Beli dengan Diamond</button>
                  @if(Auth::user()->diamond_balance < $course->diamond_price)
                    <p class="rs-note rs-note--danger">Diamond kamu belum cukup untuk kursus ini.</p>
                  @endif
                </form>
              @endif
            @endif
          @else
            <button type="button" class="rs-btn rs-btn--block" disabled>Hanya Siswa yang Bisa Mendaftar</button>
          @endif
        @else
          <a href="{{ route('login') }}" class="rs-btn rs-btn--block">Masuk untuk mendaftar</a>
        @endauth

        {{-- <h5 class="mb-1">Harga:</h5>
        <h3 class="fw-bold text-dark">
          @if($course->price > 0)
              Rp{{ number_format($course->price, 0, ',', '.') }}
          @else
              Gratis
          @endif
        </h3>

        @auth
          @if($is_enrolled)
              <a href="{{ route('student.courses.show', $course->slug) }}" class="btn btn-success w-100 mt-3">Lanjutkan Belajar</a>
          @else
              <form action="{{ route('student.cart.add', $course) }}" method="POST">
                  @csrf
                  <button type="submit" class="btn btn-primary w-100 mt-3">Tambah ke Keranjang</button>
              </form>
          @endif
        @else
          <a href="{{ route('login') }}" class="btn btn-primary w-100 mt-3">Login untuk Mendaftar</a>
        @endauth --}}
      </aside>
    </div>
  </div>
</section>

<section class="rs-section">
  @php
    $lessonTypes = [
        'App\Models\LessonArticle' => 'Artikel',
        'App\Models\LessonVideo' => 'Video',
        'App\Models\LessonDocument' => 'Dokumen',
        'App\Models\LessonLinkCollection' => 'Tautan',
        'App\Models\LessonAssignment' => 'Tugas',
        'App\Models\Quiz' => 'Kuis',
        'App\Models\LessonPoint' => 'Sesi poin',
        'App\Models\LessonDiscussion' => 'Diskusi',
        'App\Models\LessonPolling' => 'Polling',
        'App\Models\LessonWordcloud' => 'Word cloud',
    ];
  @endphp
  <div class="rs-wrap">
    <div class="rs-course-body">
      <div>
        <div class="rs-block">
          <h2 class="rs-h2">Tentang Kursus Ini</h2>
          <div class="rs-prose">
            {!! \App\Support\HtmlSanitizer::clean($course->description) !!}
          </div>
        </div>

        <div class="rs-block">
          <h2 class="rs-h2">Materi yang Akan Kamu Pelajari</h2>
          @if($course->modules->isNotEmpty())
            <div class="rs-modules">
              @foreach ($course->modules as $module)
                <details @if($loop->first) open @endif>
                  <summary><span>{{ $module->title }}</span></summary>
                  @if ($module->lessons->isEmpty())
                    <p class="rs-muted">Belum ada materi untuk modul ini.</p>
                  @else
                    <ul class="rs-lessons">
                      @foreach ($module->lessons as $lesson)
                        <li><span class="rs-tag">{{ $lessonTypes[$lesson->lessonable_type] ?? 'Materi' }}</span><span>{{ $lesson->title }}</span></li>
                      @endforeach
                    </ul>
                  @endif
                </details>
              @endforeach
            </div>
          @else
            <p class="rs-muted">Materi untuk kursus ini akan segera ditambahkan.</p>
          @endif
        </div>

        {{-- BAGIAN BARU: Daftar Ulasan Siswa --}}
        <div class="rs-block" id="ulasan">
          <h2 class="rs-h2">Ulasan dari Siswa</h2>
          @forelse ($reviews as $review)
            <article class="rs-review">
              <a href="{{ route('profile.show', $review->user->id) }}">
                <img src="{{ $review->user->profile_picture_url ? asset($review->user->profile_picture_url) : 'https://placehold.co/80x80/EBF4FF/767676?text=' . strtoupper(substr($review->user->name, 0, 1)) }}" alt="">
              </a>
              <div>
                <a class="rs-link" href="{{ route('profile.show', $review->user->id) }}">{{ $review->user->name }}</a>
                @if ($review->user->equippedBadge)
                  <span class="rs-badge">{{ $review->user->equippedBadge->title }}</span>
                @endif
                <div class="rs-meta-line">
                  <x-rs.stars :value="$review->rating" />
                  <small class="rs-muted">{{ $review->created_at->diffForHumans() }}</small>
                </div>
                <p>{{ $review->comment }}</p>
              </div>
            </article>
          @empty
            <div class="rs-empty">
              <p>Jadilah yang pertama memberikan ulasan untuk kursus ini!</p>
            </div>
          @endforelse

          {{-- Navigasi Halaman untuk Ulasan --}}
          <x-rs.pager :paginator="$reviews" />
        </div>
      </div>

      <aside class="rs-aside">
        <div>
          <h2>Instruktur</h2>
          <a class="rs-person" href="{{ route('profile.show', $course->instructor->id) }}">
            <img src="{{ $course->instructor->profile_picture_url ? asset($course->instructor->profile_picture_url) : 'https://placehold.co/80x80/EBF4FF/767676?text=' . strtoupper(substr($course->instructor->name, 0, 1)) }}" alt="" width="45" height="45">
            <span><b>{{ $course->instructor->name }}</b></span>
          </a>
        </div>
        <div>
          <h2>Kategori</h2>
          <p class="rs-muted rs-mb-0">{{ $course->category?->name }}</p>
        </div>
      </aside>
    </div>
  </div>
</section>

{{-- ================== Rekomendasi untuk Anda ================== --}}
{{-- Cek apakah ada kursus terkait sebelum menampilkan apapun --}}
@if($relatedCourses->isNotEmpty())
<section class="rs-section rs-section--paper">
  <div class="rs-wrap">
    <h2 class="rs-h2">Kursus Lain di Kategori Ini</h2>
    <div class="rs-books rs-books--4">
      @foreach ($relatedCourses as $relatedCourse)
        <x-rs.course-book :course="$relatedCourse" :show-instructor="true" />
      @endforeach
    </div>
  </div>
</section>
@endif
{{-- ================== END Rekomendasi untuk Anda ================== --}}

{{-- BAGIAN BARU: Tampilkan Rekomendasi --}}
{{-- @include('partials._recommended_courses', ['recommendedCourses' => $relatedCourses, 'title' => 'Kursus Lain di Kategori Ini']) --}}
</div>
@endsection

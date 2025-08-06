@extends('layouts.home-layout')

@section('title', $course->title)

@section('content')
<section style="background: linear-gradient(to right, #ffffff, #e0edff);">
  <div class="container">
    <div class="row align-items-start pt-5 pb-4">
      <div class="col-md-8">
        <a href="{{ route('courses') }}" class="text-muted text-decoration-none d-inline-block mb-2">
          <i class="bi bi-chevron-left"></i> Kembali ke Katalog
        </a>
        <h3 class="fw-bold mb-1 fs-1">{{ $course->title }}</h3>
        
        {{-- BAGIAN BARU: Ringkasan Rating --}}
        @if($reviewCount > 0)
        <div class="d-flex align-items-center mt-2">
            <span class="fw-bold text-warning me-2">{{ number_format($averageRating, 1) }}</span>
            <div class="star-rating-display me-2">
                @for ($i = 1; $i <= 5; $i++)
                    <i class="bi {{ $i <= round($averageRating) ? 'bi-star-fill text-warning' : 'bi-star text-muted' }}"></i>
                @endfor
            </div>
            <span class="text-muted">({{ $reviewCount }} ulasan)</span>
        </div>
        @endif

      </div>

      <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="bg-white border rounded p-4 shadow-sm">

          
          {{-- LOGIKA BARU: Tampilkan harga sesuai tipe pembayaran --}}
          @if($course->payment_type === 'money')
              <h5 class="mb-1">Harga:</h5>
              <h3 class="fw-bold text-dark">
                  @if($course->price > 0)
                      Rp{{ number_format($course->price, 0, ',', '.') }}
                  @else
                      Gratis
                  @endif
              </h3>
          @elseif($course->payment_type === 'diamonds')
              <h5 class="mb-1">Harga Diamond:</h5>
              <h3 class="fw-bold text-primary d-flex align-items-center">
                  <i class="fa fa-diamond me-2"></i> {{ number_format($course->diamond_price, 0, ',', '.') }}
              </h3>
          @endif
          
          {{-- Logika Tombol Aksi Dinamis --}}
          @auth
              @if (session('active_role') === 'student')
                @if($is_enrolled)
                    <a href="{{ route('student.courses.show', $course->slug) }}" class="btn btn-success w-100 mt-3">Lanjutkan Belajar</a>
                @else
                    @if($course->payment_type === 'money')
                        <form action="{{ route('student.cart.add', $course->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 mt-3">Tambah ke Keranjang</button>
                        </form>
                    @elseif($course->payment_type === 'diamonds')
                        <form action="{{ route('student.courses.purchase_with_diamonds', $course->id) }}" method="POST" onsubmit="return confirm('Beli kursus ini dengan {{ $course->diamond_price }} diamond?');">
                            @csrf
                            <button type="submit" class="btn btn-info w-100 mt-3" {{ Auth::user()->diamond_balance < $course->diamond_price ? 'disabled' : '' }}>
                                Beli dengan Diamond
                            </button>
                            @if(Auth::user()->diamond_balance < $course->diamond_price)
                                <small class="form-text text-danger text-center d-block mt-1">Diamond Anda tidak cukup.</small>
                            @endif
                        </form>
                    @endif
                @endif
              @else
                <button class="btn btn-primary w-100 mt-3 disabled">
                  Hanya Siswa yang Bisa Mendaftar
                </button>
              @endif
          @else
            <a href="{{ route('login') }}" class="btn btn-primary w-100 mt-3">Login untuk Mendaftar</a>
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
                <form action="{{ route('student.cart.add', $course->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100 mt-3">Tambah ke Keranjang</button>
                </form>
            @endif
          @else
            <a href="{{ route('login') }}" class="btn btn-primary w-100 mt-3">Login untuk Mendaftar</a>
          @endauth --}}

        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-white">
  <div class="container">
    <div class="row gy-5">
      <div class="col-lg-8">
        <div class="mb-4">
          <h5 class="fw-bold mb-3">Tentang Kursus Ini</h5>
          <div class="text-muted">
            {!! $course->description !!}
          </div>
        </div>

        <div class="mb-4">
          <h5 class="fw-bold">Materi yang Akan Kamu Pelajari</h5>
          <div class="accordion" id="accordionMateri">
            @forelse ($course->modules as $module)
              <div class="accordion-item border-0 mb-2 shadow-sm">
                <h2 class="accordion-header" id="heading-{{ $module->id }}">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#materi-{{ $module->id }}">
                    <i class="bi bi-card-list me-2 text-dark"></i>
                    {{ $module->title }}
                  </button>
                </h2>
                <div id="materi-{{ $module->id }}" class="accordion-collapse collapse" data-bs-parent="#accordionMateri">
                  <div class="accordion-body">
                    <ul class="list-unstyled">

                        @if ($module->lessons->isEmpty())
                          <p class="text-muted">Belum ada materi untuk modul ini.</p>
                          
                        @endif
                        @foreach ($module->lessons as $lesson)
                            @php
                              if ($lesson->lessonable_type === 'App\Models\LessonArticle') {
                                $icon = 'bi-file-text';
                              } elseif ($lesson->lessonable_type === 'App\Models\LessonVideo') {
                                $icon = 'bi-collection-play';
                              } elseif ($lesson->lessonable_type === 'App\Models\LessonDocument') {
                                $icon = 'bi-file-earmark-pdf';
                              } elseif ($lesson->lessonable_type === 'App\Models\LessonLinkCollection') {
                                $icon = 'bi-folder2-open';
                              } elseif ($lesson->lessonable_type === 'App\Models\LessonAssignment') {
                                $icon = 'bi-pencil-square';
                              } 
                              elseif ($lesson->lessonable_type === 'App\Models\Quiz') {
                                $icon = 'bi-clipboard2';
                              }
                              elseif ($lesson->lessonable_type === 'App\Models\LessonPoint') {
                                $icon = 'bi-chat-left-quote';
                              }
                              else {
                                $icon = 'fa-file-text-o';
                              }
                            @endphp
                            <li class="d-flex align-items-center mb-2 text-muted"><i class="fa {{ $icon }} me-2"></i><span>{{ $lesson->title }}</span></li>      
                        @endforeach

                    </ul>
                  </div>
                </div>
              </div>
            @empty
                <p class="text-muted">Materi untuk kursus ini akan segera ditambahkan.</p>
            @endforelse
          </div>
        </div>

        {{-- BAGIAN BARU: Daftar Ulasan Siswa --}}
        <hr class="my-5">
        <div>
            <h5 class="fw-bold mb-4">Ulasan dari Siswa</h5>
            @forelse ($reviews as $review)
                <div class="d-flex mb-4">
                    <img src="{{ $review->user->profile_picture_url ? asset('storage/' . ltrim($review->user->profile_picture_url, '/')) :  'https://placehold.co/80x80/EBF4FF/767676?text=' . strtoupper(substr($review->user->name, 0, 1)) }}" 
                         alt="{{ $review->user->name }}" 
                         class="rounded-circle me-3" 
                         style="width: 50px; height: 50px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center">
                          <h6 class="fw-bold mb-0">{{ $review->user->name }}</h6>
                          @if ($review->user->equippedBadge)
                            <span class="bg-primary rounded-pill px-2 py-1 text-white fw-bold small mx-1">
                              {{ $review->user->equippedBadge->title }}
                            </span>
                          @endif
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="star-rating-display me-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= $review->rating ? 'bi-star-fill text-warning' : 'bi-star text-muted' }}"></i>
                                @endfor
                            </div>
                            <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="text-muted">{{ $review->comment }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted p-4 bg-light rounded">
                    <p class="mb-0">Jadilah yang pertama memberikan ulasan untuk kursus ini!</p>
                </div>
            @endforelse

            {{-- Navigasi Halaman untuk Ulasan --}}
            <div class="d-flex justify-content-center mt-4">
                {{ $reviews->links() }}
            </div>
        </div>

      </div>
      <div class="col-lg-4">
        <div class="mb-4">
          <h6 class="fw-bold mb-3">Instruktur</h6>
          <div class="d-flex align-items-center">
            <img src="{{ $course->instructor->profile_picture_url ? asset('storage/' . ltrim($course->instructor->profile_picture_url, '/')) : 'https://placehold.co/80x80/EBF4FF/767676?text=' . strtoupper(substr($course->instructor->name, 0, 1)) }}"
                 class="rounded-circle me-3" style="width: 45px; height: 45px; object-fit: cover;">
            <div>
              <strong>{{ $course->instructor->name }}</strong>
            </div>
          </div>
        </div>

        <hr>
        <div>
          <h6 class="fw-bold mb-3">Kategori</h6>
          <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill mb-2 d-inline-block">
            {{ $course->category->name }}
          </span>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection
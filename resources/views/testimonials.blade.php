@extends('layouts.home-layout')

@section('title', 'Testimoni Pengguna')

@section('content')
<!-- Testimonials Section -->
<section id="testimonials" class="testimonials section light-background" style="padding-top: 120px;">

  <!-- Section Title -->
  <div class="container section-title" data-aos="fade-up">
    <h2>Pengalaman Pengguna Platform</h2>
    <p>Inilah cerita nyata dari pengguna yang sudah memanfaatkan platform kami untuk belajar dan berkembang.</p>
  </div><!-- End Section Title -->

  <div class="container">
    <div class="row g-5">
        @forelse ($allPlatformReviews as $review)
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 3 + 1) * 100 }}">
                <div class="testimonial-item">
                    <a href="{{ route('profile.show', $review->user->id) }}">
                        <img src="{{ asset($review->user->profile_picture_url ?? 'assets/profile-images/avatar-1.png') }}" class="testimonial-img" alt="">
                        <h3>{{ $review->user->name }}</h3>
                    </a>
                    <h4>{{ $review->user->studentProfile->headline ?? 'Siswa' }}</h4>
                    <div class="stars">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                        @endfor
                    </div>
                    <p>
                        <i class="bi bi-quote quote-icon-left"></i>
                        <span>{{ $review->comment }}</span>
                        <i class="bi bi-quote quote-icon-right"></i>
                    </p>
                </div>
            </div><!-- End testimonial item -->
        @empty
            <div class="col-12 text-center">
                <p class="text-muted">Belum ada testimoni yang bisa ditampilkan.</p>
            </div>
        @endforelse
    </div>
    
    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-5">
        {{ $allPlatformReviews->links() }}
    </div>
  </div>
</section><!-- /Testimonials Section -->
@endsection
@extends('layouts.home-layout')

@section('title', 'Katalog Kursus')

@section('content')

{{-- Hero: Sekarang bg-nya putih --}}
<section class="hero-section bg-white text-center" style="padding-top: 60px;">
    <div class="container mt-5" data-aos="fade-up">
        <h2 class="display-6 fw-bold mb-3">Temukan Kursus Impianmu</h2>
        <div class="mx-auto mb-3" style="width: 50px; height: 4px; background-color: #0d6efd;"></div>
        <p class="text-muted fs-5">Jelajahi berbagai pilihan kursus untuk meningkatkan keterampilan dan karier Anda.</p>
    </div>
</section>

{{-- Section utama: Sekarang pakai bg #edf8fd --}}
<section class="py-5" style="background-color: #edf8fd;">
    <div class="container">
        <form action="{{ route('courses') }}" method="GET">
            <div class="row justify-content-between align-items-center mb-4" data-aos="fade-up">
                <div class="col-md-6">
                    <h2 class="fw-bold">Kelas Terbaru</h2>
                    <p class="text-muted mb-0">Kami menyediakan kelas dengan materi yang dibuat sesuai kebutuhan di dunia Profesional.</p>
                </div>
                <div class="col-lg-3 col-md-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari judul kursus..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Sidebar Filter Kategori --}}
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">Filter Kategori</h5>
                            @foreach ($categories as $category)
                            <div class="form-check mb-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="categories[]" 
                                       value="{{ $category->id }}" 
                                       id="kategori-{{ $category->id }}"
                                       @if(is_array(request('categories')) && in_array($category->id, request('categories'))) checked @endif
                                >
                                <label class="form-check-label" for="kategori-{{ $category->id }}">{{ $category->name }}</label>
                            </div>
                            @endforeach
                            <button type="submit" class="btn btn-primary w-100 mt-3">Terapkan Filter</button>
                        </div>
                    </div>
                </div>

                {{-- Daftar Kursus --}}
                <div class="col-md-9">
                    <div class="row g-4">
                        @forelse ($courses as $course)
                            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 3 + 1) * 100 }}">
                                <a href="{{ route('courses.show', $course->slug) }}" class="text-decoration-none text-dark">
                                    <div class="card h-100 shadow-sm border-0 rounded-4 course-card">
                                        <img src="{{ $course->thumbnail_url ? Storage::url($course->thumbnail_url) : 'https://placehold.co/600x400/e0edff/007bff?text=Kursus' }}" 
                                             class="card-img-top rounded-top-4" 
                                             style="height: 150px; object-fit: cover;" 
                                             alt="{{ $course->title }}">
                                        <div class="card-body d-flex flex-column">
                                            <span class="badge bg-primary-subtle text-primary-emphasis align-self-start mb-2">{{ $course->category->name }}</span>
                                            <h5 class="card-title fw-bold text-dark flex-grow-1">{{ Str::limit($course->title, 50) }}</h5>
                                            <p class="card-text text-muted mb-2">Oleh: {{ $course->instructor->name }}</p>

                                            {{-- BAGIAN BARU: Menampilkan Rating --}}
                                            @if($course->reviews_count > 0)
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="fw-bold text-warning me-1">{{ number_format($course->reviews_avg_rating, 1) }}</span>
                                                <i class="bi bi-star-fill text-warning me-2"></i>
                                                <span class="text-muted fs-sm">({{ $course->reviews_count }} ulasan)</span>
                                            </div>
                                            @else
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bi bi-star text-muted me-2"></i>
                                                <span class="text-muted fs-sm">Belum ada ulasan</span>
                                            </div>
                                            @endif
                                            {{-- AKHIR BAGIAN BARU --}}

                                        </div>
                                        <div class="card-footer bg-white border-0 pt-0">
                                            <span class="fw-bold text-dark fs-5">

                                            {{-- DIPERBARUI: Logika untuk menampilkan harga uang atau poin --}}
                                            @if($course->payment_type === 'money')
                                                <span class="fw-bold text-dark fs-5">
                                                    @if($course->price > 0)
                                                        Rp{{ number_format($course->price, 0, ',', '.') }}
                                                    @else
                                                        Gratis
                                                    @endif
                                                </span>
                                            @elseif($course->payment_type === 'points')
                                                <span class="fw-bold text-warning fs-5 d-flex align-items-center">
                                                    <i class="fa fa-diamond me-2"></i> {{ number_format($course->points_price, 0, ',', '.') }} Poin
                                                </span>
                                            @endif

                                                {{-- @if($course->price > 0)
                                                    Rp{{ number_format($course->price, 0, ',', '.') }}
                                                @else
                                                    Gratis
                                                @endif --}}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center p-5 bg-white rounded-4 shadow-sm">
                                    <i class="bi bi-search fs-1 text-muted"></i>
                                    <h4 class="mt-3">Kursus Tidak Ditemukan</h4>
                                    <p class="text-muted">Maaf, tidak ada kursus yang cocok dengan kriteria pencarian atau filter Anda.</p>
                                    <a href="{{ route('courses') }}" class="btn btn-primary mt-2">Hapus Filter & Lihat Semua</a>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div class="d-flex justify-content-center mt-5">
                        {{ $courses->links() }}
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('styles')
<style>
    .course-card { transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; }
    .course-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
    .fs-sm { font-size: 0.875rem; }
</style>
@endpush

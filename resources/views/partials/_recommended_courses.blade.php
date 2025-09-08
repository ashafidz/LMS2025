{{-- resources/views/partials/_recommended_courses.blade.php --}}

{{-- Pastikan ada data kursus yang dikirim --}}
@if(isset($recommendedCourses) && $recommendedCourses->isNotEmpty())
<div class="row mt-5">
    <div class="col-12">
        <h3 class="fw-bold mb-3">{{ $title ?? 'Rekomendasi Kursus' }}</h3>
        
        {{-- Wrapper untuk horizontal scroll --}}
        <div class="horizontal-scroll-wrapper">
            @foreach($recommendedCourses as $course)
                {{-- Setiap item memiliki lebar tetap agar bisa di-scroll --}}
                <div class="scroll-item">
                    <a href="{{ route('courses.show', $course->slug) }}" class="text-decoration-none text-dark">
                        <div class="card h-100 shadow-sm border-0 rounded-4 course-card">
                            <img src="{{ $course->thumbnail_url ? Storage::url($course->thumbnail_url) : 'https://placehold.co/600x400' }}" 
                                 class="card-img-top rounded-top-4" 
                                 style="height: 150px; object-fit: cover;" 
                                 alt="{{ $course->title }}">
                            <div class="card-body d-flex flex-column">
                                <span class="badge bg-primary-subtle text-primary-emphasis align-self-start mb-2">{{ $course->category->name }}</span>
                                <h5 class="card-title fw-bold text-dark flex-grow-1">{{ Str::limit($course->title, 50) }}</h5>
                                <p class="card-text text-muted mb-2">Oleh: {{ $course->instructor->name }}</p>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0">
                                @if($course->payment_type === 'money')
                                    <span class="fw-bold text-dark fs-5">
                                        @if($course->price > 0)
                                            Rp{{ number_format($course->price, 0, ',', '.') }}
                                        @else
                                            Gratis
                                        @endif
                                    </span>
                                @elseif($course->payment_type === 'diamonds')
                                    <span class="fw-bold text-info fs-5 d-flex align-items-center">
                                        <i class="fa fa-diamond me-2"></i> {{ number_format($course->diamond_price, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- CSS untuk horizontal scroll --}}
@push('styles')
<style>
    .horizontal-scroll-wrapper {
        display: flex;
        overflow-x: auto;
        padding-bottom: 1rem;
        -webkit-overflow-scrolling: touch; /* Untuk scroll yang mulus di iOS */
    }
    /* Sembunyikan scrollbar */
    .horizontal-scroll-wrapper::-webkit-scrollbar {
        display: none;
    }
    .scroll-item {
        flex: 0 0 280px; /* Lebar setiap kartu */
        margin-right: 1.5rem;
    }
    .course-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }
</style>
@endpush
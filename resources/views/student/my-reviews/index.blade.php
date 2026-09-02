@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Kelola Ulasan Saya</h5>
                        <p class="m-b-0">Lihat dan perbarui semua ulasan yang telah Anda berikan.</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Ulasan Saya</a></li>
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
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-lg-3 col-md-4 mb-4">
                            <!-- Sidebar Navigation for Tabs -->
                            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                                <div class="card-block p-3">
                                    <div class="nav flex-column nav-pills custom-v-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                        <a class="nav-link active mb-2" id="v-pills-platform-tab" data-toggle="pill" href="#v-pills-platform" role="tab" aria-controls="v-pills-platform" aria-selected="true">
                                            <i class="fa fa-desktop mr-2"></i> Ulasan Platform
                                        </a>
                                        <a class="nav-link mb-2" id="v-pills-course-tab" data-toggle="pill" href="#v-pills-course" role="tab" aria-controls="v-pills-course" aria-selected="false">
                                            <i class="fa fa-book mr-2"></i> Ulasan Kursus
                                        </a>
                                        <a class="nav-link" id="v-pills-instructor-tab" data-toggle="pill" href="#v-pills-instructor" role="tab" aria-controls="v-pills-instructor" aria-selected="false">
                                            <i class="fa fa-user-circle mr-2"></i> Ulasan Instruktur
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-9 col-md-8">
                            <div class="tab-content" id="v-pills-tabContent">
                                
                                <!-- PLATFORM TAB -->
                                <div class="tab-pane fade show active" id="v-pills-platform" role="tabpanel" aria-labelledby="v-pills-platform-tab">
                                    <div class="card shadow-sm border-0" style="border-radius: 12px;">
                                        <div class="card-block p-4">
                                            <h5 class="font-weight-bold mb-4">Ulasan Anda Tentang Platform Ini</h5>
                                            
                                            @if($platformReview)
                                                <div class="text-center py-5 bg-light mb-4" style="border-radius: 12px; border: 1px dashed #ced4da;">
                                                    <h2 class="text-warning mb-3">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fa {{ $i <= $platformReview->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                                                        @endfor
                                                    </h2>
                                                    @if($platformReview->comment)
                                                        <p class="text-dark font-italic mb-3">"{{ $platformReview->comment }}"</p>
                                                    @endif
                                                    <p class="text-muted mb-0 small">Terima kasih atas ulasan Anda! Umpan balik Anda sangat berarti bagi kami.</p>
                                                </div>
                                                <div class="text-center">
                                                    <button class="btn btn-primary btn-round px-4 shadow-sm" data-toggle="modal" data-target="#platformReviewModal">
                                                        <i class="fa fa-edit mr-1"></i> Perbarui Ulasan
                                                    </button>
                                                </div>
                                            @else
                                                <div class="text-center py-5">
                                                    <i class="fa fa-comment-o text-muted f-50 opacity-50 mb-3"></i>
                                                    <h5 class="text-muted font-weight-bold">Belum Ada Ulasan</h5>
                                                    <p class="text-muted mb-4">Bantu kami menjadi lebih baik dengan memberikan ulasan mengenai pengalaman Anda menggunakan platform ini.</p>
                                                    <button class="btn btn-primary btn-round px-4 shadow-sm" data-toggle="modal" data-target="#platformReviewModal">
                                                        <i class="fa fa-pencil mr-1"></i> Beri Ulasan Sekarang
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- COURSE TAB -->
                                <div class="tab-pane fade" id="v-pills-course" role="tabpanel" aria-labelledby="v-pills-course-tab">
                                    <div class="card shadow-sm border-0" style="border-radius: 12px;">
                                        <div class="card-block p-4">
                                            <h5 class="font-weight-bold mb-4">Ulasan Kursus Anda</h5>
                                            
                                            @forelse ($courseReviews as $review)
                                                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                                                    <div class="pr-3">
                                                        <h6 class="font-weight-bold mb-1">{{ $review->course->title }}</h6>
                                                        <div class="text-warning mb-1" style="font-size: 14px;">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fa {{ $i <= $review->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                                                            @endfor
                                                        </div>
                                                        <p class="text-muted mb-0 small text-truncate" style="max-width: 400px;" title="{{ $review->comment }}">
                                                            {{ $review->comment ?? 'Tidak ada komentar tertulis.' }}
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-light btn-sm btn-round text-primary shadow-sm" data-toggle="modal" data-target="#courseReviewModal-{{ $review->id }}">
                                                            <i class="fa fa-edit"></i> Edit
                                                        </button>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center py-5">
                                                    <i class="fa fa-book text-muted f-50 opacity-50 mb-3"></i>
                                                    <p class="text-muted mb-0">Anda belum memberikan ulasan untuk kursus manapun.</p>
                                                </div>
                                            @endforelse
                                            
                                            <div class="d-flex justify-content-center mt-4">
                                                {{ $courseReviews->links('pagination::bootstrap-4') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- INSTRUCTOR TAB -->
                                <div class="tab-pane fade" id="v-pills-instructor" role="tabpanel" aria-labelledby="v-pills-instructor-tab">
                                    <div class="card shadow-sm border-0" style="border-radius: 12px;">
                                        <div class="card-block p-4">
                                            <h5 class="font-weight-bold mb-4">Ulasan Instruktur Anda</h5>
                                            
                                            @forelse ($instructorReviews as $review)
                                                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                                                    <div class="pr-3">
                                                        <h6 class="font-weight-bold mb-1">{{ $review->instructor->name }}</h6>
                                                        <p class="text-muted small mb-1">Kursus: <strong>{{ $review->course->title }}</strong></p>
                                                        <div class="text-warning mb-1" style="font-size: 14px;">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fa {{ $i <= $review->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                                                            @endfor
                                                        </div>
                                                        <p class="text-muted mb-0 small text-truncate" style="max-width: 400px;" title="{{ $review->comment }}">
                                                            {{ $review->comment ?? 'Tidak ada komentar tertulis.' }}
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-light btn-sm btn-round text-primary shadow-sm" data-toggle="modal" data-target="#instructorReviewModal-{{ $review->id }}">
                                                            <i class="fa fa-edit"></i> Edit
                                                        </button>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center py-5">
                                                    <i class="fa fa-user-circle text-muted f-50 opacity-50 mb-3"></i>
                                                    <p class="text-muted mb-0">Anda belum memberikan ulasan untuk instruktur manapun.</p>
                                                </div>
                                            @endforelse
                                            
                                            <div class="d-flex justify-content-center mt-4">
                                                {{ $instructorReviews->links('pagination::bootstrap-4') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS --}}
    @include('student.my-reviews.partials._platform_review_modal')
    @foreach($instructorReviews as $review)
        @include('student.my-reviews.partials._instructor_review_modal', ['review' => $review])
    @endforeach
    @foreach($courseReviews as $review)
        @include('student.my-reviews.partials._course_review_modal', ['review' => $review])
    @endforeach
@endsection

{{-- Styling khusus untuk rating bintang di modal --}}
@push('styles')
<style>
    .star-rating { display: inline-block; direction: rtl; }
    .star-rating input { display: none; }
    .star-rating label { font-size: 2.5rem; color: #ddd; cursor: pointer; }
    .star-rating input:checked ~ label, .star-rating label:hover, .star-rating label:hover ~ label { color: #f5b301; }
    .likert-scale .btn-check:checked+.btn { background-color: #007bff; color: white; }
    
    /* Styling for Vertical Pills Navigation */
    .custom-v-pills .nav-link {
        color: #6c757d;
        font-weight: 500;
        padding: 12px 20px;
        transition: all 0.3s ease;
        border-radius: 8px;
        border: none;
    }
    .custom-v-pills .nav-link:hover {
        background-color: #f8f9fa;
        color: #333;
    }
    .custom-v-pills .nav-link.active {
        background-color: #e3f2fd !important;
        color: #1E88E5 !important;
        font-weight: 600;
        border: none !important;
    }
</style>
@endpush
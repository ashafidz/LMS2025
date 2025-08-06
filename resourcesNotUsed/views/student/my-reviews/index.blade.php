@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Kelola Ulasan Saya</h5>
                        <p class="m-b-0">Lihat dan perbarui semua ulasan yang telah Anda berikan.</p>
                    </div>
                </div>
                <div class="col-md-4">
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

                    <!-- BAGIAN ULASAN PLATFORM -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Ulasan Anda Tentang Platform Ini</h5>
                        </div>
                        <div class="card-block">
                            @if($platformReview)
                                {{-- Jika sudah pernah memberi ulasan --}}
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p>Terima kasih atas ulasan Anda! Anda bisa memperbaruinya kapan saja.</p>
                                        <p class="mb-0"><strong>Rating Anda:</strong> 
                                            <span class="text-warning">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fa {{ $i <= $platformReview->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                                            @endfor
                                            </span>
                                        </p>
                                    </div>
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#platformReviewModal">Edit Ulasan</button>
                                </div>
                            @else
                                {{-- Jika belum pernah memberi ulasan --}}
                                <div class="text-center">
                                    <p>Anda belum memberikan ulasan untuk platform kami. Umpan balik Anda sangat berharga untuk membantu kami menjadi lebih baik.</p>
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#platformReviewModal">Beri Ulasan Platform</button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- BAGIAN ULASAN INSTRUKTUR -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Ulasan Anda untuk Instruktur</h5>
                        </div>
                        <div class="card-block">
                            @forelse ($instructorReviews as $review)
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                    <div>
                                        <h6 class="mb-1">{{ $review->instructor->name }}</h6>
                                        <p class="text-muted mb-1">Dalam kursus: <strong>{{ $review->course->title }}</strong></p>
                                        <p class="mb-0">
                                            <span class="text-warning">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fa {{ $i <= $review->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                                            @endfor
                                            </span>
                                        </p>
                                    </div>
                                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#instructorReviewModal-{{ $review->id }}">Edit</button>
                                </div>
                            @empty
                                <p class="text-muted">Anda belum memberikan ulasan untuk instruktur manapun.</p>
                            @endforelse
                            <div class="d-flex justify-content-center">
                                {{ $instructorReviews->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN ULASAN KURSUS -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Ulasan Anda untuk Kursus</h5>
                        </div>
                        <div class="card-block">
                            @forelse ($courseReviews as $review)
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                    <div>
                                        <h6 class="mb-1">{{ $review->course->title }}</h6>
                                        <p class="mb-0">
                                            <span class="text-warning">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fa {{ $i <= $review->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                                            @endfor
                                            </span>
                                        </p>
                                    </div>
                                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#courseReviewModal-{{ $review->id }}">Edit</button>
                                </div>
                            @empty
                                <p class="text-muted">Anda belum memberikan ulasan untuk kursus manapun.</p>
                            @endforelse
                            <div class="d-flex justify-content-center">
                                {{ $courseReviews->links('pagination::bootstrap-4') }}
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
</style>
@endpush
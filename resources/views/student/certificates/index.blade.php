@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Sertifikat Saya</h5>
                        <p class="m-b-0">Daftar sertifikat kursus milik {{ Auth::user()->name }}</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item">
                            <a href="{{ route('student.dashboard') }}"><i class="fa fa-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#!">Sertifikat</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">
                        @forelse ($certificates as $certificate)
                            <div class="col-sm-6 col-lg-4 mb-4">
                                <div class="card shadow-sm h-100 border-0" style="border-radius: 12px; transition: transform 0.2s ease;">
                                    <div class="card-block text-center p-4 d-flex flex-column">
                                        <div class="mb-4">
                                            <div class="d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 50%; background: rgba(255, 193, 7, 0.1);">
                                                <i class="fa fa-certificate text-warning f-40"></i>
                                            </div>
                                        </div>
                                        <h5 class="font-weight-bold mb-3" style="line-height: 1.4;">{{ $certificate->course->title }}</h5>
                                        
                                        <div class="mt-auto">
                                            <div class="bg-light p-2 rounded mb-3">
                                                <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 12px;">
                                                    <span title="Tanggal Diperoleh"><i class="fa fa-calendar mr-1"></i> {{ $certificate->issued_at->format('d M Y') }}</span>
                                                    <span title="Kode Sertifikat"><i class="fa fa-hashtag mr-1"></i> {{ $certificate->certificate_code }}</span>
                                                </div>
                                            </div>
                                            <a href="{{ route('student.certificate.download', $certificate->course_id) }}" class="btn btn-primary btn-sm btn-block btn-round download-cert-btn shadow-sm">
                                                <i class="fa fa-download mr-1"></i> Unduh Sertifikat
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                                    <div class="card-block text-center py-5">
                                        <div class="mb-3">
                                            <i class="fa fa-certificate text-muted f-50 opacity-50"></i>
                                        </div>
                                        <h5 class="text-muted mb-2 font-weight-bold">Belum Ada Sertifikat</h5>
                                        <p class="text-muted mb-4">Selesaikan kursus Anda untuk mendapatkan sertifikat pencapaian yang dapat dibagikan.</p>
                                        <a href="{{ route('courses') }}" class="btn btn-outline-primary btn-round">
                                            Jelajahi Kursus <i class="fa fa-arrow-right ml-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    
                    @if($certificates->hasPages())
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-center mt-2">
                                {{ $certificates->links() }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Loading Animation Modal -->
    <div id="loadingModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="loadingLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <h5 id="loadingLabel" class="mt-3">Mengunduh Sertifikat...</h5>
                    <p class="text-muted">Harap tunggu, sertifikat Anda sedang diproses.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .custom-card-header {
        background: #fff !important;
        border-bottom: 1px solid #e0e0e0;
        border-left: 4px solid #1E88E5;
        padding: 12px 16px;
    }
    .table td {
        vertical-align: middle;
    }

    /* Loading Modal Styles */
    .modal.show {
        background-color: rgba(0, 0, 0, 0.5);
    }

    .spinner-border {
        display: inline-block;
        width: 3rem;
        height: 3rem;
        vertical-align: text-bottom;
        animation: spinner-border 0.75s linear infinite;
    }

    @keyframes spinner-border {
        to {
            transform: rotate(360deg);
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const downloadButtons = document.querySelectorAll('.download-cert-btn');
    const loadingModal = document.getElementById('loadingModal');
    const bsModal = new bootstrap.Modal(loadingModal);

    downloadButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            // Show loading modal
            bsModal.show();

            // Auto-hide modal after 8 seconds (PDF generation usually takes 3-5 seconds)
            setTimeout(function () {
                bsModal.hide();
            }, 8000);

            // Optional: hide modal when download starts (visual feedback)
            // This requires additional server-side implementation for better UX
        });
    });
});
</script>
@endpush
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
                        <div class="col-sm-12">
                            <div class="card shadow-sm mb-4">
                                <div class="card-header custom-card-header">
                                    <h6 class="mb-0 fw-bold text-dark">Rincian Sertifikat</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:5%;">No</th>
                                                    <th>Nama Kursus</th>
                                                    <th>Kode Sertifikat</th>
                                                    <th style="width:18%;">Tanggal Peroleh</th>
                                                    <th style="width:12%;" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($certificates as $certificate)
                                                <tr>
                                                    <td>{{ $loop->iteration + $certificates->firstItem() - 1 }}</td>
                                                    <td>{{ $certificate->course->title }}</td>
                                                    <td>{{ $certificate->certificate_code }}</td>
                                                    <td>{{ $certificate->issued_at->format('d F Y') }}</td>
                                                    <td class="text-center">
                                                        <a href="{{ route('student.certificate.download', $certificate->course_id) }}" class="btn btn-sm btn-success download-cert-btn">
                                                            <i class="fa fa-download"></i> Unduh
                                                        </a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">Anda belum memiliki sertifikat.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $certificates->links() }}
                                    </div>
                                </div>
                            </div><!-- End Card -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

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
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
                                                        <a href="{{ route('student.certificate.download', $certificate->course_id) }}" class="btn btn-sm btn-success">
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
</style>
@endpush
@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Menjadi Instruktur</h5>
                        <p class="m-b-0">Lengkapi profil Anda untuk mulai mengajar.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Daftar Instruktur</a></li>
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
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Formulir Pendaftaran Instruktur</h5>
                                    <span>Isi data di bawah ini dengan lengkap. Tim kami akan meninjaunya dalam 1-3 hari kerja.</span>
                                </div>
                                <div class="card-block">
                                    <form action="{{ route('student.apply_instructor.store') }}" method="POST">
                                        @csrf
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Jabatan Singkat</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="headline" class="form-control" value="{{ old('headline') }}" required placeholder="Contoh: Web Developer, Digital Marketer">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Bio / Perkenalan Diri</label>
                                            <div class="col-sm-9">
                                                <textarea name="bio" class="form-control" rows="5" required placeholder="Ceritakan tentang keahlian dan pengalaman Anda...">{{ old('bio') }}</textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Website / Portofolio (Opsional)</label>
                                            <div class="col-sm-9">
                                                <input type="url" name="website_url" class="form-control" value="{{ old('website_url') }}" placeholder="https://linkedin.com/in/namaanda">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Nomor Induk (Opsional)</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="unique_id_number" class="form-control" value="{{ old('unique_id_number') }}" placeholder="NIM/NIP/NIDN...">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-12 text-right">
                                                <button type="submit" class="btn btn-primary">Kirim Pendaftaran</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
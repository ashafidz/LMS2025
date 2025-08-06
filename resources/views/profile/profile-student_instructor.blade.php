@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block py-1">
                <div class="bg-transparent d-inline-flex align-items-center px-4 py-3 rounded"
                    style="background: rgba(0,0,0,0.1); backdrop-filter: blur(2px);">
                    <!-- Avatar -->
                    <img src="{{ asset($user->profile_picture_url ? 'storage/' . $user->profile_picture_url : 'https://placehold.co/32x32/EBF4FF/767676?text=SA') }}"
                        alt="Avatar" class="rounded-circle mr-4" style="width: 80px; height: 80px; border: 3px solid white;">

                    <!-- Nama & Sambutan -->
                    <div>
                        <h4 class="text-white font-weight-bold mb-1">{{ $user->name }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page-header end -->
        <div class="pcoded-inner-content px-3 mt-4">
            <div class="row g-4 px-2">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Biografi</h5>
                        </div>
                        <div class="card-block">
                            <!-- Bio -->
                            <div class="row">
                                <div class="col-md-12">
                                    <p class="mb-1">
                                        {{ $profile->bio ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Data Profil (kiri) -->
                <div class="col-xl-8 col-md-12 mb-4 pr-xl-2">
                    <div class="card">
                        <div class="card-header">
                            <h5>Informasi Personal</h5>
                        </div>
                        <div class="card-block">
                            <div class="row">
                                <div class="col-md-6 mb-3 d-flex">
                                    <div style="width: 24px;">
                                        <i class="ti ti-calendar text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="mb-1 font-weight-bold">Tanggal Lahir</p>
                                        <p class="mb-0">{{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('d F Y') : '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3 d-flex">
                                    <div style="width: 24px;">
                                        <i class="ti ti-briefcase text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="mb-1 font-weight-bold">Pekerjaan / Profesi</p>
                                        <p class="mb-0">{{ $profile->profession ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3 d-flex">
                                    <div style="width: 24px;">
                                        <i class="bi bi-gender-ambiguous text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="mb-1 font-weight-bold">Gender</p>
                                        <p class="mb-0">{{ $user->gender ? ucfirst($user->gender) : '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3 d-flex">
                                    <div style="width: 24px;">
                                        <i class="ti ti-id-badge text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="mb-1 font-weight-bold">Headline</p>
                                        <p class="mb-0">{{ $profile->headline ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3 d-flex">
                                    <div style="width: 24px;">
                                        <i class="ti ti-location-pin text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="mb-1 font-weight-bold">Alamat</p>
                                        <p class="mb-0">{{ $user->address ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3 d-flex">
                                    <div style="width: 24px;">
                                        <i class="ti ti-world text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="mb-1 font-weight-bold">Website</p>
                                        <p class="mb-0">{{ $profile->website_url ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-0 d-flex">
                                    <div style="width: 24px;">
                                        <i class="bi bi-person-plus text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="mb-1 font-weight-bold">Bergabung Pada</p>
                                        <p class="mb-0">{{ $user->created_at->format('d F Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <!-- Card Data Tambahan -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Data Tambahan</h5>
                        </div>
                        <div class="card-block">
                            <div class="row">
                                <div class="col-md-6 mb-3 d-flex">
                                    <div style="width: 24px;">
                                        <i class="bi bi-building text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="mb-1 font-weight-bold">Nama Perusahaan / Institusi Saat Ini</p>
                                        <p class="mb-0">{{ $profile->company_or_institution_name ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3 d-flex">
                                    <div style="width: 24px;">
                                        <i class="ti ti-map-alt text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="mb-1 font-weight-bold">Alamat Perusahaan</p>
                                        <p class="mb-0">{{ $profile->company_address ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-0 d-flex">
                                    <div style="width: 24px;">
                                        <i class="bi bi-receipt-cutoff text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="mb-1 font-weight-bold">No. NPWP Perusahaan</p>
                                        <p class="mb-0">{{ $profile->company_tax_id ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-0 d-flex">
                                    <div style="width: 24px;">
                                        <i class="bi bi-person-badge text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="mb-1 font-weight-bold">NIM / NIP / NIDN</p>
                                        <p class="mb-0">{{ $profile->unique_id_number ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kontak (kanan) -->
                <div class="col-xl-4 col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Kontak</h5>
                        </div>
                        <div class="card-block">
                
                            <!-- Email -->
                            <div class="mb-3">
                                <div class="d-flex align-items-start">
                                    <div style="width: 24px;">
                                        <i class="ti ti-email text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="mb-1 font-weight-bold">Email</p>
                                        <p class="mb-0 text-muted">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </div>
                
                            <!-- Telepon -->
                            <div class="mb-0">
                                <div class="d-flex align-items-start">
                                    <div style="width: 24px;">
                                        <i class="bi bi-telephone text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="mb-1 font-weight-bold">Telepon</p>
                                        <p class="mb-0 text-muted">{{ $user->phone_number ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 row g-2">
                        <div class="col-6">
                            <a href="{{ route('user.profile.edit') }}" class="btn btn-primary btn-sm w-100">Edit Profile</a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('user.password.edit') }}" class="btn btn-primary btn-sm w-100">Ganti Password</a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- project and team member end -->

        </div>
    </div>
@endsection
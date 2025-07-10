@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block py-1">
                <div class="bg-transparent d-inline-flex align-items-center px-4 py-3 rounded"
                    style="background: rgba(0,0,0,0.1); backdrop-filter: blur(2px);">
                    <!-- Avatar -->
                    <img src="{{ asset($user->profile_picture_url ?? 'https://placehold.co/32x32/EBF4FF/767676?text=SA') }}" alt="Avatar"
                        class="rounded-circle mr-4" style="width: 80px; height: 80px; border: 3px solid white;">

                    <!-- Nama & Sambutan -->
                    <div>
                        <h4 class="text-white font-weight-bold mb-1">{{ $user->name }}</h4>
                        <span class="text-white">Your current role is: {{ ucfirst(session('active_role')) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page-header end -->
        <div class="pcoded-inner-content px-3 mt-4">
            <div class="row g-4 px-2">

                <!-- Kolom Data Profil (kiri) -->
                <div class="col-xl-8 col-md-12 mb-4 pr-xl-2">
                    <div class="card">
                        <div class="card-header">
                            <h5>Informasi Personal</h5>
                        </div>
                        <div class="card-block">

                            <!-- Grid data pribadi -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3 d-flex">
                                    <img src="{{ asset('assets/icon/profile/cake.svg') }}" class="img-20 mr-2"
                                        alt="">
                                    <div>
                                        <p class="mb-1 font-weight-semibold">Tanggal Lahir</p>
                                        <p class="mb-0">
                                            {{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('d F Y') : '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3 d-flex">
                                    <img src="{{ asset('assets/icon/profile/user.svg') }}" class="img-20 mr-2"
                                        alt="">
                                    <div>
                                        <p class="mb-1 font-weight-semibold">Gender</p>
                                        <p class="mb-0">{{ $user->gender ? ucfirst($user->gender) : '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3 d-flex">
                                    <img src="{{ asset('assets/icon/profile/home.svg') }}" class="img-20 mr-2"
                                        alt="">
                                    <div>
                                        <p class="mb-1 font-weight-semibold">Alamat</p>
                                        <p class="mb-0">{{ $user->address ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3 d-flex">
                                    <img src="{{ asset('assets/icon/profile/user-add.svg') }}" class="img-20 mr-2"
                                        alt="">
                                    <div>
                                        <p class="mb-1 font-weight-semibold">Bergabung Pada</p>
                                        <p class="mb-0">{{ $user->created_at->format('d F Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kontak (kanan) -->
                <div class="col-xl-4 col-md-12 mb-4 ">
                    <div class="card">
                        <div class="card-header">
                            <h5>Kontak</h5>
                        </div>
                        <div class="card-block">
                            <div class="mb-3">
                                <p class="mb-1 font-weight-semibold">Email</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">{{ $user->email }}</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <p class="mb-1 font-weight-semibold">Telepon</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">{{ $user->phone_number ?? '-' }}</span>
                                    <a href="#" class="text-primary small">✎</a>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <a href="{{ route('user.profile.edit') }}"
                                    class="btn btn-outline-primary btn-sm mr-2">Edit Profile</a>
                                <a href="{{ route('user.password.edit') }}" class="btn btn-outline-primary btn-sm mr-2">Ganti
                                    Password</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

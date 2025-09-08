@extends('layouts.app-layout')

@section('content')
  <!-- Pre-loader end -->
  <div id="pcoded" class="pcoded">
      <div class="pcoded-overlay-box"></div>
      <div class="pcoded-container navbar-wrapper">

          <div class="pcoded-main-container">
              <div class="pcoded-wrapper">
                  <div class="pcoded-content">
                      <!-- Page-header start -->
                      <div class="page-header">
                          <div class="page-block py-1">
                          <div class="bg-transparent d-inline-flex align-items-center px-4 py-3 rounded" style="background: rgba(0,0,0,0.1); backdrop-filter: blur(2px);">
                              <!-- Avatar -->
                              <img src="assets/images/avatar-4.jpg" alt="Avatar" class="rounded-circle mr-4" style="width: 80px; height: 80px; border: 3px solid white;">
                        
                              <!-- Nama & Sambutan -->
                              <div>
                              <h4 class="text-white font-weight-bold mb-1">John Doe</h4>
                              </div>
                          </div>
                          </div>
                      </div>

                      <!-- Card Biografi -->
                        <div class="col-12 mb-2 mt-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Biografi</h5>
                                </div>
                                <div class="card-block">
                                    <p class="mb-0"> - </p>
                                </div>
                            </div>
                        </div>

                      <!-- Page-header end -->
                        <div class="pcoded-inner-content px-3 mt-4">
                            <div class="row g-4 px-2">
            
                            <!-- Kolom Data Profil (kiri) -->
                            <div class="col-xl-8 col-md-12 mb-4 pr-xl-2">
                                <!-- Card Informasi Personal -->
                                <div class="card">
                                <div class="card-header">
                                    <h5>Informasi Personal</h5>
                                </div>
                                <div class="card-block">
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 d-flex">
                                            <i class="bi bi-person-fill text-lg mr-2"></i>
                                            <div>
                                                <p class="mb-1"><strong>NIM/NIP</strong></p>
                                                <p class="mb-0">-</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3 d-flex">
                                            <i class="bi bi-cake2-fill text-lg mr-2"></i>
                                            <div>
                                                <p class="mb-1"><strong>Tanggal Lahir</strong></p>
                                                <p class="mb-0">1 January 0001</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3 d-flex">
                                            <i class="bi bi-building text-lg mr-2"></i>
                                            <div>
                                                <p class="mb-1"><strong>Pekerjaan / Profesi</strong></p>
                                                <p class="mb-0">UI/UX</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3 d-flex">
                                            <i class="bi bi-gender-male text-lg mr-2"></i>
                                            <div>
                                                <p class="mb-1"><strong>Gender</strong></p>
                                                <p class="mb-0">Laki-laki</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3 d-flex">
                                            <i class="bi bi-globe text-lg mr-2"></i>
                                            <div>
                                                <p class="mb-1"><strong>Website</strong></p>
                                                <p class="mb-0">-</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3 d-flex">
                                            <i class="bi bi-house-fill text-lg mr-2"></i>
                                            <div>
                                                <p class="mb-1"><strong>Alamat</strong></p>
                                                <p class="mb-0">Jl. Mawar</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3 d-flex">
                                            <i class="bi bi-person-plus-fill text-lg mr-2"></i>
                                            <div>
                                                <p class="mb-1"><strong>Bergabung Pada</strong></p>
                                                <p class="mb-0">7 July 2025</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>                               
                                </div>
                            
                                <!-- Card Data Tambahan -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h5>Data Tambahan</h5>
                                    </div>
                                    <div class="card-block">
                                        <div class="row mb-3">
                                            <div class="col-md-6 d-flex mb-3">
                                                <i class="bi bi-building text-lg mr-2"></i>
                                                <div>
                                                    <p class="mb-1"><strong>Nama Perusahaan / Institusi Saat Ini</strong></p>
                                                    <p class="mb-0">Politeknik Negeri Malang</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 d-flex mb-3">
                                                <i class="bi bi-geo-alt-fill text-lg mr-2"></i>
                                                <div>
                                                    <p class="mb-1"><strong>Alamat Perusahaan</strong></p>
                                                    <p class="mb-0">Jl. Telo</p>
                                                </div>
                                            </div>
                                            <div class="col-md-12 d-flex">
                                                <i class="bi bi-file-earmark-text-fill text-lg mr-2"></i>
                                                <div>
                                                    <p class="mb-1"><strong>No. NPWP Perusahaan</strong></p>
                                                    <p class="mb-0">-</p>
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
                                        <div class="mb-3 d-flex">
                                            <i class="bi bi-envelope-fill text-lg mr-2 mt-1"></i>
                                            <div>
                                                <p class="mb-1"><strong>Email</strong></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted">bigsdaddies@gmail.com</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 d-flex">
                                            <i class="bi bi-telephone-fill text-lg mr-2 mt-1"></i>
                                            <div class="flex-grow-1">
                                                <p class="mb-1"><strong>Telepon</strong></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <p class="mb-0">-</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                                        
                                <!-- BUTTONS: Edit Profile dan Ganti Password -->
                                <div class="mt-3 row g-2">
                                    <div class="col-6">
                                      <a href="1edit-index.html" class="btn btn-outline-primary btn-sm w-100">Edit Profile</a>
                                    </div>
                                    <div class="col-6">
                                      <a href="1ganti-pass.html" class="btn btn-outline-primary btn-sm w-100">Ganti Password</a>
                                    </div>
                                  </div>                                 
                            </div>

                            </div>
                      
                            <!-- project and team member end -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endsection
    


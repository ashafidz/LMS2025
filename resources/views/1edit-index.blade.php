@extends('layouts.app-layout')

@section('content')

          <div class="pcoded-main-container">
              <div class="pcoded-wrapper">
                  <div class="pcoded-content">
                    <div class="pcoded-inner-content px-3" style="padding-top: 32px;">
                        <div class="row g-4 px-2">

                            <!-- Avatar & Aksi -->
                            <div class="col-xl-4 col-md-12 mb-4">
                                <div class="card text-center">
                                    <div class="card-body py-4">
                                        <h6 class="mb-3">Foto Profil</h6>
                                        <img src="assets/images/avatar-4.jpg" class="img-100 img-radius mb-3" alt="User-Profile-Image">
                                        <button class="btn btn-outline-primary btn-sm btn-block" data-bs-toggle="modal" data-bs-target="#avatarModal">
                                            Pilih Avatar
                                        </button>                                        
                                    </div>
                                    <!-- Modal Pilih Avatar -->
                                    <div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                            <h5 class="modal-title" id="avatarModalLabel">Pilih Avatar</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                            </div>
                                            <div class="modal-body">
                                            <div class="row">
                                                <!-- Avatar 1 -->
                                                <div class="col-3 mb-3">
                                                <img src="assets/images/avatar-1.jpg" class="img-fluid rounded-circle border avatar-option" style="cursor:pointer;" onclick="selectAvatar('assets/images/avatar-1.jpg')">
                                                </div>
                                                <!-- Avatar 2 -->
                                                <div class="col-3 mb-3">
                                                <img src="assets/images/avatar-2.jpg" class="img-fluid rounded-circle border avatar-option" style="cursor:pointer;" onclick="selectAvatar('assets/images/avatar-2.jpg')">
                                                </div>
                                                <!-- Avatar 3 -->
                                                <div class="col-3 mb-3">
                                                <img src="assets/images/avatar-3.jpg" class="img-fluid rounded-circle border avatar-option" style="cursor:pointer;" onclick="selectAvatar('assets/images/avatar-2.jpg')">
                                                </div>
                                                <!-- Avatar 4 -->
                                                <div class="col-3 mb-3">
                                                <img src="assets/images/avatar-4.jpg" class="img-fluid rounded-circle border avatar-option" style="cursor:pointer;" onclick="selectAvatar('assets/images/avatar-2.jpg')">
                                                </div>
                                                <!-- Avatar 5 -->
                                                <div class="col-3 mb-3">
                                                <img src="assets/images/avatar-5.jpg" class="img-fluid rounded-circle border avatar-option" style="cursor:pointer;" onclick="selectAvatar('assets/images/avatar-2.jpg')">
                                                </div>
                                                <!-- Avatar 6 -->
                                                <div class="col-3 mb-3">
                                                <img src="assets/images/avatar-6.jpg" class="img-fluid rounded-circle border avatar-option" style="cursor:pointer;" onclick="selectAvatar('assets/images/avatar-2.jpg')">
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
  
                                </div>
                            </div> 

                            <!-- Form Edit Profil -->
                            <div class="col-xl-8 col-md-12 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Edit Biografi</h5>
                                    </div>
                                    <div class="card-block">
                                        <form action="#" method="POST">
                                            <div class="form-group">
                                                <label for="biografi">Biografi Singkat</label>
                                                <textarea class="form-control" id="biografi" name="biografi" rows="6" placeholder="Tulis cerita singkat tentang dirimu..."></textarea>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="card ">
                                    <div class="card-header">
                                        <h5>Edit Profil Siswa</h5>
                                    </div>
                                    <div class="card-block">
                                        <form action="#" method="POST">
                                            <!-- Biodata -->
                                            <div class="form-group">
                                                <label for="nama">NIM / NIP</label>
                                                <input type="text" class="form-control" id="nama" placeholder="23317xxx">
                                            </div>
                                            <div class="form-group">
                                                <label for="nama">Nama Lengkap</label>
                                                <input type="text" class="form-control" id="nama" placeholder="Contoh: John Doe">
                                            </div>

                                            <div class="form-group">
                                                <label for="tanggal_lahir">Tanggal Lahir</label>
                                                <input type="date" class="form-control" id="tanggal_lahir">
                                            </div>

                                            <div class="form-group">
                                                <label for="gender">Jenis Kelamin</label>
                                                <select class="form-control" id="gender">
                                                    <option>Pilih</option>
                                                    <option>Laki-laki</option>
                                                    <option>Perempuan</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="nama">Nomor Telepon</label>
                                                <input type="text" class="form-control" id="nama" placeholder="Contoh: +62 096535566">
                                            </div>

                                            <div class="form-group">
                                                <label for="alamat">Alamat Lengkap</label>
                                                <textarea class="form-control" id="alamat" rows="2" placeholder="Contoh: Jl. Mawar No.10, Kec. Sukolilo"></textarea>
                                            </div>

                                            <div class="form-group">
                                                <label for="pendidikan">Pendidikan Terakhir</label>
                                                <select class="form-control" id="pendidikan">
                                                    <option>Pilih Pendidikan</option>
                                                    <option>SD</option>
                                                    <option>SMP</option>
                                                    <option>SMA / SMK</option>
                                                    <option>D3</option>
                                                    <option>S1</option>
                                                    <option>S2</option>
                                                    <option>S3</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="job">Pekerjaan / Profesi </label>
                                                <input type="job" class="form-control" id="job" placeholder="Contoh: UI/UX Designer">
                                            </div>

                                            <div class="form-group">
                                                <label for="url">Website</label>
                                                <input type="url" class="form-control" id="url" placeholder="Contoh: link/LinkedID">
                                            </div>

                                            <!-- Pendidikan -->
                                            <hr>
                                            <h6 class="text-primary font-weight-bold mt-4">Data Perusahaan</h6>
                                            <p></p>
                                            <div class="form-group">
                                                <label for="pt">Nama Perusahaan / Institusi Saat Ini</label>
                                                <input type="text" class="form-control" id="pt" placeholder="Contoh: PT. LinkedID">
                                            </div>

                                            <div class="form-group">
                                                <label for="alamatpt">Alamat Perusahaan</label>
                                                <textarea class="form-control" id="alamatpt" rows="2" placeholder="Contoh: Jl. MasMismus"></textarea>
                                            </div>

                                            <div class="form-group">
                                                <label for="npwp">No. NPWP Perusahaan</label>
                                                <input type="text" class="form-control" id="npwp" placeholder="Contoh: 12XXX">
                                            </div>

                                            <!-- Tombol -->
                                            <div class="mt-4 text-right">
                                                <a href="1student.html" class="btn btn-outline-secondary btn-sm">Batal</a>
                                                <button type="button" onclick="window.location.href='1student.html'" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>                                                
                            <!-- project and team member end -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
{{--     
    <!-- Required Jquery -->
    <script type="text/javascript" src="assets/js/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="assets/js/jquery-ui/jquery-ui.min.js "></script>
    <script type="text/javascript" src="assets/js/popper.js/popper.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js "></script>
    <script type="text/javascript" src="assets/pages/widget/excanvas.js "></script>
    <!-- waves js -->
    <script src="assets/pages/waves/js/waves.min.js"></script>
    <!-- jquery slimscroll js -->
    <script type="text/javascript" src="assets/js/jquery-slimscroll/jquery.slimscroll.js "></script>
    <!-- modernizr js -->
    <script type="text/javascript" src="assets/js/modernizr/modernizr.js "></script>
    <!-- slimscroll js -->
    <script type="text/javascript" src="assets/js/SmoothScroll.js"></script>
    <script src="assets/js/jquery.mCustomScrollbar.concat.min.js "></script>
    <!-- Chart js -->
    <script type="text/javascript" src="assets/js/chart.js/Chart.js"></script>
    <!-- amchart js -->
    <script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
    <script src="assets/pages/widget/amchart/gauge.js"></script>
    <script src="assets/pages/widget/amchart/serial.js"></script>
    <script src="assets/pages/widget/amchart/light.js"></script>
    <script src="assets/pages/widget/amchart/pie.min.js"></script>
    <script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
    <!-- menu js -->
    <script src="assets/js/pcoded.min.js"></script>
    <script src="assets/js/vertical-layout.min.js "></script>
    <!-- custom js -->
    <script type="text/javascript" src="assets/pages/dashboard/custom-dashboard.js"></script>
    <script type="text/javascript" src="assets/js/script.js "></script>
 --}}


@endsection


@push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectAvatar(path) {
          const avatarImg = document.querySelector('.card-body img');
          avatarImg.src = path;
      
          // Tutup modal setelah pilih
          const modalEl = document.getElementById('avatarModal');
          const modalInstance = bootstrap.Modal.getInstance(modalEl);
          if (modalInstance) modalInstance.hide();
        }
      </script>
@endpush
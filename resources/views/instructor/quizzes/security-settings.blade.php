@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Pengaturan Keamanan Kuis</h5>
                            <p class="m-b-0">{{ $quiz->title }}</p>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex mt-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.courses.modules.index', $quiz->lesson->module->course) }}">Modul</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $quiz->lesson->module) }}">{{ Str::limit($quiz->lesson->module->title, 20) }}</a></li>
                            <li class="breadcrumb-item"><a href="#!">Keamanan Kuis</a></li>
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
                                <!-- Alert Info -->
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <i class="fa fa-info-circle"></i>
                                    <strong>Info:</strong> Pengaturan keamanan ini akan aktif saat siswa mengerjakan kuis. Anda dapat mengaktifkan atau menonaktifkan fitur sesuai kebutuhan.
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <!-- Card Form -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Opsi Keamanan Kuis</h5>
                                        <span>Aktifkan fitur keamanan untuk mencegah kecurangan selama kuis berlangsung.</span>
                                    </div>
                                    <div class="card-block">
                                        <form id="securitySettingsForm">
                                            @csrf

                                            <!-- 1. Deteksi Kamera -->
                                            <div class="card mb-3 border">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-10">
                                                            <h6 class="font-weight-bold mb-2">
                                                                <i class="fa fa-video text-primary"></i> Deteksi Kamera (Face Detection)
                                                            </h6>
                                                            <p class="text-muted mb-2">
                                                                Aktifkan kamera untuk mendeteksi wajah siswa selama mengerjakan kuis. Sistem akan mendeteksi jika siswa menoleh, menunduk, atau wajah tidak terdeteksi.
                                                            </p>
                                                            <small class="text-muted">
                                                                <i class="fa fa-lightbulb"></i> Teknologi: MediaPipe Face Mesh + TensorFlow.js
                                                            </small>
                                                        </div>
                                                        <div class="col-md-2 text-right">
                                                            <label class="switch-modern">
                                                                <input type="checkbox" name="enable_camera_detection" 
                                                                       id="enableCamera" 
                                                                       value="1"
                                                                       {{ $securitySetting->enable_camera_detection ? 'checked' : '' }}>
                                                                <span class="slider-modern round"></span>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Advanced Settings Camera -->
                                                    <div id="cameraSettings" class="mt-3 pt-3 border-top" 
                                                         style="display: {{ $securitySetting->enable_camera_detection ? 'block' : 'none' }}">
                                                        <!-- Tipe Pelanggaran yang Dideteksi -->
                                                        <div class="form-group">
                                                            <label class="font-weight-bold mb-2"><i class="fa fa-filter text-info"></i> Tipe Pelanggaran yang Dideteksi</label>
                                                            <small class="form-text text-muted mb-2">Nonaktifkan tipe pelanggaran yang tidak ingin dideteksi.</small>
                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <div class="checkbox-fade fade-in-default">
                                                                        <label>
                                                                            <input type="checkbox" name="detect_face_not_detected" value="1"
                                                                                   {{ $securitySetting->detect_face_not_detected ? 'checked' : '' }}>
                                                                            <span class="cr"><i class="cr-icon fa fa-check"></i></span>
                                                                            <span>Wajah Tidak Terdeteksi</span>
                                                                        </label>
                                                                    </div>
                                                                    <div class="checkbox-fade fade-in-default">
                                                                        <label>
                                                                            <input type="checkbox" name="detect_look_left" value="1"
                                                                                   {{ $securitySetting->detect_look_left ? 'checked' : '' }}>
                                                                            <span class="cr"><i class="cr-icon fa fa-check"></i></span>
                                                                            <span>Menoleh Kiri</span>
                                                                        </label>
                                                                    </div>
                                                                    <div class="checkbox-fade fade-in-default">
                                                                        <label>
                                                                            <input type="checkbox" name="detect_look_right" value="1"
                                                                                   {{ $securitySetting->detect_look_right ? 'checked' : '' }}>
                                                                            <span class="cr"><i class="cr-icon fa fa-check"></i></span>
                                                                            <span>Menoleh Kanan</span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <div class="checkbox-fade fade-in-default">
                                                                        <label>
                                                                            <input type="checkbox" name="detect_look_up" value="1"
                                                                                   {{ $securitySetting->detect_look_up ? 'checked' : '' }}>
                                                                            <span class="cr"><i class="cr-icon fa fa-check"></i></span>
                                                                            <span>Melihat Ke Atas</span>
                                                                        </label>
                                                                    </div>
                                                                    <div class="checkbox-fade fade-in-default">
                                                                        <label>
                                                                            <input type="checkbox" name="detect_look_down" value="1"
                                                                                   {{ $securitySetting->detect_look_down ? 'checked' : '' }}>
                                                                            <span class="cr"><i class="cr-icon fa fa-check"></i></span>
                                                                            <span>Menunduk / Melihat Ke Bawah</span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <hr class="mt-2 mb-3">
                                                        <div class="form-group row">
                                                            <label class="col-sm-5 col-form-label">Batas Toleransi Pelanggaran Kamera</label>
                                                            <div class="col-sm-7">
                                                                <input type="number" name="camera_violation_threshold" 
                                                                       class="form-control" 
                                                                       value="{{ $securitySetting->camera_violation_threshold }}"
                                                                       min="1" max="20">
                                                                <small class="form-text text-muted">Jumlah pelanggaran sebelum ditandai untuk review</small>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-5 col-form-label">Interval Deteksi (Detik)</label>
                                                            <div class="col-sm-7">
                                                                <input type="number" name="face_detection_interval_seconds" 
                                                                       class="form-control" 
                                                                       value="{{ $securitySetting->face_detection_interval_seconds }}"
                                                                       min="3" max="30">
                                                                <small class="form-text text-muted">Seberapa sering sistem memeriksa wajah siswa</small>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-sm-5 col-form-label">Durasi Pelanggaran (Detik)</label>
                                                            <div class="col-sm-7">
                                                                <input type="number" name="violation_duration_seconds" 
                                                                       class="form-control" 
                                                                       value="{{ $securitySetting->violation_duration_seconds ?? 3 }}"
                                                                       min="0" max="10">
                                                                <small class="form-text text-muted">Berapa detik pelanggaran harus berlangsung sebelum dihitung. Nilai 0 = langsung dihitung.</small>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-2">
                                                        <span class="badge badge-pill" id="badgeCamera">
                                                            {{ $securitySetting->enable_camera_detection ? 'AKTIF' : 'NONAKTIF' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- 2. Deteksi Perpindahan Tab -->
                                            <div class="card mb-3 border">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-10">
                                                            <h6 class="font-weight-bold mb-2">
                                                                <i class="fa fa-window-restore text-warning"></i> Deteksi Perpindahan Tab
                                                            </h6>
                                                            <p class="text-muted mb-2">
                                                                Sistem akan mencatat setiap kali siswa berpindah tab browser atau aplikasi lain selama mengerjakan kuis.
                                                            </p>
                                                            <small class="text-muted">
                                                                <i class="fa fa-lightbulb"></i> Teknologi: JavaScript Page Visibility API
                                                            </small>
                                                        </div>
                                                        <div class="col-md-2 text-right">
                                                            <label class="switch-modern">
                                                                <input type="checkbox" name="enable_tab_detection" 
                                                                       id="enableTab" 
                                                                       value="1"
                                                                       {{ $securitySetting->enable_tab_detection ? 'checked' : '' }}>
                                                                <span class="slider-modern round"></span>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <!-- Advanced Settings Tab -->
                                                    <div id="tabSettings" class="mt-3 pt-3 border-top" 
                                                         style="display: {{ $securitySetting->enable_tab_detection ? 'block' : 'none' }}">
                                                        <div class="form-group row">
                                                            <label class="col-sm-5 col-form-label">Batas Toleransi Perpindahan Tab</label>
                                                            <div class="col-sm-7">
                                                                <input type="number" name="tab_violation_threshold" 
                                                                       class="form-control" 
                                                                       value="{{ $securitySetting->tab_violation_threshold }}"
                                                                       min="1" max="50">
                                                                <small class="form-text text-muted">Jumlah perpindahan tab sebelum ditandai untuk review</small>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-2">
                                                        <span class="badge badge-pill" id="badgeTab">
                                                            {{ $securitySetting->enable_tab_detection ? 'AKTIF' : 'NONAKTIF' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- 3. Pengacakan Soal -->
                                            <div class="card mb-3 border">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-10">
                                                            <h6 class="font-weight-bold mb-2">
                                                                <i class="fa fa-random text-success"></i> Pengacakan Soal (Fisher-Yates Shuffle)
                                                            </h6>
                                                            <p class="text-muted mb-2">
                                                                Setiap siswa akan mendapatkan urutan soal yang berbeda secara acak untuk mengurangi kemungkinan mencontek.
                                                            </p>
                                                            <small class="text-muted">
                                                                <i class="fa fa-lightbulb"></i> Algoritma: Fisher-Yates Shuffle
                                                            </small>
                                                        </div>
                                                        <div class="col-md-2 text-right">
                                                            <label class="switch-modern">
                                                                <input type="checkbox" name="enable_question_shuffle" 
                                                                       id="enableShuffle" 
                                                                       value="1"
                                                                       {{ $securitySetting->enable_question_shuffle ? 'checked' : '' }}>
                                                                <span class="slider-modern round"></span>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="mt-2">
                                                        <span class="badge badge-pill" id="badgeShuffle">
                                                            {{ $securitySetting->enable_question_shuffle ? 'AKTIF' : 'NONAKTIF' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Buttons -->
                                            <div class="form-group row mt-4">
                                                <div class="col-sm-12 text-right">
                                                    <a href="{{ route('instructor.modules.lessons.index', $quiz->lesson->module) }}" 
                                                       class="btn btn-secondary">
                                                        <i class="fa fa-arrow-left"></i> Kembali
                                                    </a>
                                                    <button type="button" class="btn btn-danger" id="btnReset">
                                                        <i class="fa fa-undo"></i> Reset ke Default
                                                    </button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fa fa-save"></i> Simpan Pengaturan
                                                    </button>
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

@push('styles')
<style>
/* Modern Toggle Switch */
.switch-modern {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch-modern input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider-modern {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
}

.slider-modern:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
}

input:checked + .slider-modern {
    background-color: #2ed8b6;
}

input:focus + .slider-modern {
    box-shadow: 0 0 1px #2ed8b6;
}

input:checked + .slider-modern:before {
    transform: translateX(26px);
}

.slider-modern.round {
    border-radius: 34px;
}

.slider-modern.round:before {
    border-radius: 50%;
}

/* Badge Styling */
.badge-pill {
    padding: 0.4rem 0.8rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-pill.badge-success {
    background-color: #2ed8b6;
}

.badge-pill.badge-secondary {
    background-color: #6c757d;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle Camera Settings
    $('#enableCamera').change(function() {
        if($(this).is(':checked')) {
            $('#cameraSettings').slideDown();
            $('#badgeCamera').removeClass('badge-secondary').addClass('badge-success').text('AKTIF');
        } else {
            $('#cameraSettings').slideUp();
            $('#badgeCamera').removeClass('badge-success').addClass('badge-secondary').text('NONAKTIF');
        }
    });

    // Toggle Tab Settings
    $('#enableTab').change(function() {
        if($(this).is(':checked')) {
            $('#tabSettings').slideDown();
            $('#badgeTab').removeClass('badge-secondary').addClass('badge-success').text('AKTIF');
        } else {
            $('#tabSettings').slideUp();
            $('#badgeTab').removeClass('badge-success').addClass('badge-secondary').text('NONAKTIF');
        }
    });

    // Toggle Shuffle Badge
    $('#enableShuffle').change(function() {
        if($(this).is(':checked')) {
            $('#badgeShuffle').removeClass('badge-secondary').addClass('badge-success').text('AKTIF');
        } else {
            $('#badgeShuffle').removeClass('badge-success').addClass('badge-secondary').text('NONAKTIF');
        }
    });

    // Submit Form via AJAX
    $('#securitySettingsForm').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        
        // Disable button & show loading
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: '{{ route("instructor.quiz.security.update", $quiz) }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if(response.success) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan pengaturan.'
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

    // Reset Button
    $('#btnReset').click(function() {
        Swal.fire({
            title: 'Reset ke Default?',
            text: "Semua pengaturan keamanan akan dinonaktifkan dan dikembalikan ke nilai default.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Reset!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("instructor.quiz.security.destroy", $quiz) }}',
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan.'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush
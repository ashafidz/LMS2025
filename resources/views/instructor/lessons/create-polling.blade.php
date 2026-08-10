@extends('layouts.app-layout')
@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Buat Pelajaran Polling Baru</h5>
                        <p class="m-b-0">Buat polling untuk mendapatkan pendapat dari siswa Anda.</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.courses.modules.index', $module->course) }}">Modul Saya</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $module) }}">{{ Str::limit($module->title, 20) }}</a></li>
                        <li class="breadcrumb-item"><a href="#!">Buat Polling</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="pcoded-inner-content">
        <div class="main-body"><div class="page-wrapper"><div class="page-body">
            <div class="row"><div class="col-sm-12">
                <div class="card">
                    <div class="card-header"><h5>Detail Polling</h5></div>
                    <div class="card-block">
                        <form action="{{ route('instructor.modules.lessons.store', $module) }}" method="POST" data-lesson-submit="true">
                            @csrf
                            <input type="hidden" name="lesson_type" value="polling">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                <div class="col-sm-10">
                                    <input type="text" name="title" class="form-control" required placeholder="Contoh: Polling Pemahaman Materi">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Pertanyaan Polling</label>
                                <div class="col-sm-10">
                                    <input type="text" name="polling_question" class="form-control" required placeholder="Contoh: Apa pendapat Anda tentang materi ini?">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Deskripsi (Opsional)</label>
                                <div class="col-sm-10">
                                    <textarea name="polling_description" class="form-control" rows="4"></textarea>
                                </div>
                            </div>

                            <h5 class="mt-4">Opsi Polling</h5>
                            <div id="polling-options-container">
                                <div class="form-group row option-row">
                                    <label class="col-sm-2 col-form-label">Opsi 1</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="polling_options[]" class="form-control" required>
                                    </div>
                                    <div class="col-sm-1"></div>
                                </div>
                                <div class="form-group row option-row">
                                    <label class="col-sm-2 col-form-label">Opsi 2</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="polling_options[]" class="form-control" required>
                                    </div>
                                    <div class="col-sm-1"></div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-2"></div>
                                <div class="col-sm-10">
                                    <button type="button" class="btn btn-info btn-sm" id="add-option-btn"><i class="fa fa-plus"></i> Tambah Opsi</button>
                                </div>
                            </div>

                            <h5 class="mt-4">Pengaturan (Opsional)</h5>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Aktif</label>
                                <div class="col-sm-10">
                                    <div class="checkbox-fade fade-in-primary">
                                        <label>
                                            <input type="checkbox" name="is_active" value="1" checked>
                                            <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                            <span class="text-inverse">Apakah polling ini dapat diakses siswa?</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Waktu Mulai</label>
                                <div class="col-sm-10">
                                    <input type="datetime-local" name="start_time" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Waktu Selesai</label>
                                <div class="col-sm-10">
                                    <input type="datetime-local" name="end_time" class="form-control">
                                </div>
                            </div>

                            <div class="form-group row mt-4">
                                <div class="col-sm-12 text-right">
                                    <a href="{{ route('instructor.modules.lessons.index', $module) }}" class="btn btn-secondary">Batal</a>
                                    <button type="submit" class="btn btn-primary js-lesson-submit-btn">
                                        <span class="js-lesson-submit-text">Simpan Pelajaran</span>
                                        <span class="spinner-border spinner-border-sm ms-2 d-none js-lesson-submit-spinner" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div></div>
        </div></div></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('add-option-btn').addEventListener('click', function() {
        const container = document.getElementById('polling-options-container');
        const optionCount = container.querySelectorAll('.option-row').length + 1;
        
        const row = document.createElement('div');
        row.className = 'form-group row option-row';
        row.innerHTML = `
            <label class="col-sm-2 col-form-label">Opsi ${optionCount}</label>
            <div class="col-sm-9">
                <input type="text" name="polling_options[]" class="form-control" required>
            </div>
            <div class="col-sm-1 text-right">
                <button type="button" class="btn btn-danger btn-sm remove-option-btn"><i class="fa fa-trash"></i></button>
            </div>
        `;
        
        container.appendChild(row);
        
        row.querySelector('.remove-option-btn').addEventListener('click', function() {
            row.remove();
            updateOptionLabels();
        });
    });

    function updateOptionLabels() {
        const rows = document.querySelectorAll('.option-row');
        rows.forEach((row, index) => {
            row.querySelector('label').textContent = 'Opsi ' + (index + 1);
        });
    }
</script>
@endpush

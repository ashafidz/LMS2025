@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Buat Pelajaran Baru</h5>
                            <p class="m-b-0">Tipe: Kumpulan Link</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.courses.modules.index', $module->course) }}">Modul</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $module) }}">{{ Str::limit($module->title, 20) }}</a></li>
                            <li class="breadcrumb-item"><a href="#!">Buat Link</a></li>
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
                                        <h5>Detail Pelajaran Kumpulan Link</h5>
                                        <span>Isi detail untuk pelajaran baru Anda.</span>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.modules.lessons.store', $module) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="lesson_type" value="link">

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Contoh: Referensi Belajar Tambahan">
                                                </div>
                                            </div>

                                            <hr>

                                            <h5 class="mt-4">Daftar Tautan</h5>
                                            <p class="mb-4">Tambahkan satu atau lebih tautan referensi untuk pelajaran ini.</p>

                                            {{-- Kontainer untuk baris-baris input link dinamis --}}
                                            <div id="links-container">
                                                {{-- Baris input akan ditambahkan di sini oleh JavaScript --}}
                                            </div>

                                            <div class="row mt-3">
                                                <div class="col-sm-12">
                                                    <button type="button" class="btn btn-success" id="add-link-btn">
                                                        <i class="fa fa-plus"></i> Tambah Tautan Lain
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="form-group row mt-5">
                                                <div class="col-sm-12 text-right">
                                                    <a href="{{ route('instructor.modules.lessons.index', $module) }}" class="btn btn-secondary">Batal</a>
                                                    <button type="submit" class="btn btn-primary">Simpan Pelajaran</button>
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

    {{-- Template HTML tersembunyi untuk baris link baru --}}
    <template id="link-template">
        <div class="form-group row align-items-center link-row">
            <div class="col-sm-5">
                <label>Judul Tautan</label>
                <input type="text" name="links[__INDEX__][title]" class="form-control" placeholder="Contoh: Dokumentasi Laravel" required>
            </div>
            <div class="col-sm-5">
                <label>URL Tautan</label>
                <input type="url" name="links[__INDEX__][url]" class="form-control" placeholder="https://laravel.com/docs" required>
            </div>
            <div class="col-sm-2">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-danger btn-block" onclick="this.closest('.link-row').remove()">
                    <i class="fa fa-trash"></i> Hapus
                </button>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tambahkan satu baris input secara default saat halaman dimuat
        addLinkRow();
    });

    document.getElementById('add-link-btn').addEventListener('click', function() {
        addLinkRow();
    });

    function addLinkRow() {
        // Ambil konten dari template
        const template = document.getElementById('link-template').innerHTML;
        // Buat index unik untuk menghindari konflik nama input
        const newIndex = Date.now();
        // Ganti placeholder __INDEX__ dengan index unik yang baru
        const newRowHtml = template.replace(/__INDEX__/g, newIndex);

        // Tambahkan baris baru ke dalam kontainer
        document.getElementById('links-container').insertAdjacentHTML('beforeend', newRowHtml);
    }
</script>
@endpush
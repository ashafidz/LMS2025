@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content rs-page-form">
        <!-- Page-header start -->
        <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], [\Illuminate\Support\Str::limit($lesson->module->course->title, 30), route('instructor.courses.modules.index', $lesson->module->course)], [(Str::limit($lesson->module->title, 20)), route('instructor.modules.lessons.index', $lesson->module)], ['Edit Link', null]]">
            <x-slot:context>Kursus: {{ $lesson->module->course->title }}  >  Modul: {{ $lesson->module->title }}</x-slot:context>
            <x-slot:title>Edit Pelajaran</x-slot:title>
            <x-slot:subtitle>Tipe: Kumpulan Link</x-slot:subtitle>
        </x-dash.page-head>
        <!-- Page-header end -->

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Edit Detail Pelajaran Kumpulan Link</h5>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.lessons.update', $lesson) }}" method="POST" class="rs-form">
                                            @csrf
                                            @include('instructor.lessons.partials._form-errors')
                                            @method('PUT')

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control" value="{{ old('title', $lesson->title) }}" required>
                                                </div>
                                            </div>

                                            <div class="rs-form-section"><h3>Daftar Tautan</h3></div>
                                            <p class="mb-4">Ubah, hapus, atau tambahkan tautan referensi untuk pelajaran ini.</p>

                                            <div id="links-container">
                                                @php
                                                    // Ambil data link dari old input atau dari database
                                                    $links = old('links', $lesson->lessonable->links);
                                                @endphp

                                                @if($links)
                                                    @foreach ($links as $index => $link)
                                                        <div class="form-group row align-items-center link-row">
                                                            <div class="col-sm-5">
                                                                <label>Judul Tautan</label>
                                                                <input type="text" name="links[{{ $index }}][title]" class="form-control" value="{{ $link['title'] ?? '' }}" required>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <label>URL Tautan</label>
                                                                <input type="url" name="links[{{ $index }}][url]" class="form-control" value="{{ $link['url'] ?? '' }}" required>
                                                            </div>
                                                            <div class="col-sm-2">
                                                                <label>&nbsp;</label>
                                                                <button type="button" class="btn btn-outline-danger btn-block" onclick="this.closest('.link-row').remove()">
                                                                    <i class="fa fa-trash"></i> Hapus
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>

                                            <div class="row mt-3">
                                                <div class="col-sm-12">
                                                    <button type="button" class="btn btn-secondary" id="add-link-btn">
                                                        <i class="fa fa-plus"></i> Tambah Tautan Lain
                                                    </button>
                                                </div>
                                            </div>

                                                                                        <div class="rs-form-actions">
                                                <a href="{{ route('instructor.modules.lessons.index', $lesson->module) }}" class="btn btn-secondary">Batal</a>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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
                <button type="button" class="btn btn-outline-danger btn-block" onclick="this.closest('.link-row').remove()">
                    <i class="fa fa-trash"></i> Hapus
                </button>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
<script>
    document.getElementById('add-link-btn').addEventListener('click', function() {
        // Ambil konten dari template
        const template = document.getElementById('link-template').innerHTML;
        // Buat index unik untuk menghindari konflik nama input
        const newIndex = Date.now();
        // Ganti placeholder __INDEX__ dengan index unik yang baru
        const newRowHtml = template.replace(/__INDEX__/g, newIndex);

        // Tambahkan baris baru ke dalam kontainer
        document.getElementById('links-container').insertAdjacentHTML('beforeend', newRowHtml);
    });
</script>
@endpush
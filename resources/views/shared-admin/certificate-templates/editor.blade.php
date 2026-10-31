@extends('layouts.app-layout')

@section('content')
@php
    $isEdit = $template !== null;
    $orientation = old('orientation', $template->orientation ?? 'landscape');
    $initialElements = old('elements') ? array_values(old('elements')) : ($template->elements ?? []);
    $backgroundUrl = $isEdit ? $template->backgroundUrl() : '';
    $pageTitle = $isEdit ? "Ubah Template (dari Versi {$template->version})" : 'Buat Template Sertifikat';
@endphp
<style>
    {!! \App\Support\CertificateFonts::fontFaceCss(false) !!}
    .cert-canvas-wrap { background: #f4f5f7; padding: 12px; border: 1px solid #ddd; }
    .cert-canvas { position: relative; width: 100%; background: #fff; overflow: hidden; user-select: none; }
    .cert-canvas img.cert-bg { position: absolute; left: 0; top: 0; width: 100%; height: 100%; pointer-events: none; }
    .cert-canvas .cert-empty { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #888; pointer-events: none; text-align: center; padding: 20px; }
    .cert-el { position: absolute; cursor: move; touch-action: none; border: 1px dashed rgba(0, 123, 255, .6); line-height: 1.2; box-sizing: border-box; overflow-wrap: anywhere; }
    .cert-el.selected { border: 2px solid #007bff; background: rgba(0, 123, 255, .08); }
    .cert-el .ui-resizable-e { position: absolute; right: -4px; top: 0; width: 8px; height: 100%; cursor: e-resize; background: #007bff; opacity: .6; }
    .cert-guide-v, .cert-guide-h { position: absolute; background: #e83e8c; display: none; pointer-events: none; }
    .cert-guide-v { left: 50%; top: 0; width: 1px; height: 100%; }
    .cert-guide-h { top: 50%; left: 0; height: 1px; width: 100%; }
    .element-list button { text-align: left; }
    #element-settings { display: none; }
</style>
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">{{ $pageTitle }}</h5>
                        <p class="m-b-0">Pilih gambar latar, lalu seret tulisan ke tempat yang diinginkan. Setelah selesai, simpan sebagai versi baru.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.certificate-templates.index') }}">Template Sertifikat</a></li>
                        <li class="breadcrumb-item"><a href="#!">{{ $isEdit ? 'Ubah' : 'Buat' }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Template belum bisa disimpan:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="template-form" method="POST" enctype="multipart/form-data"
                        action="{{ route($routePrefix . '.certificate-templates.store') }}"
                        data-preview-url="{{ route($routePrefix . '.certificate-templates.preview-draft') }}"
                        data-background-url="{{ $backgroundUrl }}"
                        data-orientation="{{ $orientation }}"
                        data-elements="{{ json_encode($initialElements) }}"
                        data-fonts="{{ json_encode($fontFamilies) }}"
                        data-bold-fonts="{{ json_encode($boldFonts) }}"
                        data-italic-fonts="{{ json_encode($italicFonts) }}"
                        data-labels="{{ json_encode($elementLabels) }}">
                        @csrf
                        @if ($isEdit)
                            <input type="hidden" name="based_on_id" value="{{ $template->id }}">
                        @endif
                        <input type="hidden" name="orientation" id="orientation-input" value="{{ $orientation }}">
                        <div id="elements-inputs"></div>

                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header"><h5>Langkah 1: Gambar latar</h5></div>
                                    <div class="card-block">
                                        <div class="form-group">
                                            <label for="background">Gambar latar sertifikat (PNG atau JPG)</label>
                                            <input type="file" class="form-control" id="background" name="background" accept="image/png,image/jpeg">
                                            <small class="form-text text-muted">
                                                Disarankan 3508 x 2480 piksel untuk sertifikat mendatar (landscape), maksimal 5 MB.
                                                @if ($isEdit) Kosongkan jika ingin tetap memakai gambar versi ini. @endif
                                            </small>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="mr-3">Arah kertas:</label>
                                            <label class="mr-3"><input type="radio" name="orientation_choice" id="orient-landscape" value="landscape"> Mendatar (landscape)</label>
                                            <label><input type="radio" name="orientation_choice" id="orient-portrait" value="portrait"> Tegak (portrait)</label>
                                        </div>
                                        <div id="ratio-warning" class="alert alert-warning mt-3 mb-0" style="display: none;">
                                            Rasio gambar tidak sama dengan kertas A4, gambar akan direntang.
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header"><h5>Langkah 2: Atur posisi tulisan</h5></div>
                                    <div class="card-block">
                                        <p class="text-muted">Seret kotak untuk memindahkan. Tarik sisi kanan kotak untuk mengubah lebar. Klik kotak untuk mengatur huruf. Garis merah muda muncul saat tulisan tepat di tengah.</p>
                                        <div class="cert-canvas-wrap">
                                            <div id="cert-canvas" class="cert-canvas">
                                                <img id="cert-bg" class="cert-bg" alt="" style="display: none;">
                                                <div id="cert-empty" class="cert-empty">Pilih gambar latar di Langkah 1 untuk mulai.</div>
                                                <div class="cert-guide-v" id="guide-v"></div>
                                                <div class="cert-guide-h" id="guide-h"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header"><h5>Tambahkan ke sertifikat</h5></div>
                                    <div class="card-block">
                                        @foreach ($elementLabels as $key => $label)
                                            @if ($key !== 'custom_text')
                                                <div class="form-check">
                                                    <input class="form-check-input js-add-element" type="checkbox" id="add-{{ $key }}" data-key="{{ $key }}" @if ($key === 'student_name') checked disabled @endif>
                                                    <label class="form-check-label" for="add-{{ $key }}">{{ $label }}@if ($key === 'student_name') (wajib)@endif</label>
                                                </div>
                                            @endif
                                        @endforeach
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-custom-text">+ Teks Bebas</button>
                                        <hr>
                                        <h6>Elemen di sertifikat</h6>
                                        <div id="element-list" class="element-list"></div>
                                    </div>
                                </div>

                                <div class="card" id="element-settings">
                                    <div class="card-header"><h5 id="settings-title">Pengaturan elemen</h5></div>
                                    <div class="card-block">
                                        <div class="form-group" id="group-text">
                                            <label for="ctl-text">Isi tulisan</label>
                                            <input type="text" class="form-control" id="ctl-text" maxlength="200">
                                        </div>
                                        <div class="form-group" id="group-date-format">
                                            <label for="ctl-date-format">Format tanggal</label>
                                            <select class="form-control no-select2" id="ctl-date-format">
                                                <option value="long">24 Oktober 2026</option>
                                                <option value="short">24/10/2026</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="ctl-font">Jenis huruf</label>
                                            <select class="form-control no-select2" id="ctl-font">
                                                @foreach ($fonts as $fontKey => $fontLabel)
                                                    <option value="{{ $fontKey }}">{{ $fontLabel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-6">
                                                <label for="ctl-size">Ukuran huruf</label>
                                                <input type="number" class="form-control" id="ctl-size" min="6" max="96" step="1">
                                            </div>
                                            <div class="form-group col-6">
                                                <label for="ctl-color">Warna</label>
                                                <input type="color" class="form-control" id="ctl-color" style="height: 38px; padding: 2px;">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="d-block">Perataan</label>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary js-align" data-align="left" title="Rata kiri"><i class="fa fa-align-left"></i></button>
                                                <button type="button" class="btn btn-outline-secondary js-align" data-align="center" title="Rata tengah"><i class="fa fa-align-center"></i></button>
                                                <button type="button" class="btn btn-outline-secondary js-align" data-align="right" title="Rata kanan"><i class="fa fa-align-right"></i></button>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="mr-3"><input type="checkbox" id="ctl-bold"> Tebal</label>
                                            <label><input type="checkbox" id="ctl-italic"> Miring</label>
                                            <small class="form-text text-muted" id="style-hint" style="display: none;">Jenis huruf ini tidak punya pilihan tebal atau miring.</small>
                                        </div>
                                        <div class="form-group">
                                            <label><input type="checkbox" id="ctl-shrink"> Kecilkan otomatis jika teks terlalu panjang</label>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-6">
                                                <label for="ctl-x">Dari kiri (%)</label>
                                                <input type="number" class="form-control" id="ctl-x" min="0" max="100" step="0.1">
                                            </div>
                                            <div class="form-group col-6">
                                                <label for="ctl-y">Dari atas (%)</label>
                                                <input type="number" class="form-control" id="ctl-y" min="0" max="100" step="0.1">
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="ctl-delete">Hapus elemen</button>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header"><h5>Langkah 3: Simpan</h5></div>
                                    <div class="card-block">
                                        <div class="form-group">
                                            <label for="name">Nama desain</label>
                                            <input type="text" class="form-control" id="name" name="name" maxlength="100" required value="{{ old('name', $template?->name) }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="change_note">Catatan perubahan (tidak wajib)</label>
                                            <input type="text" class="form-control" id="change_note" name="change_note" maxlength="255" value="{{ old('change_note') }}"
                                                placeholder="Contoh: nama dipindah ke tengah">
                                        </div>
                                        <button type="button" class="btn btn-outline-info btn-block" id="btn-preview">
                                            <i class="fa fa-file-pdf-o"></i> Pratinjau PDF
                                        </button>
                                        <button type="submit" class="btn btn-primary btn-block" id="btn-save">
                                            Simpan sebagai Versi Baru
                                        </button>
                                        <a href="{{ route($routePrefix . '.certificate-templates.index') }}" class="btn btn-link btn-block">Batal</a>
                                        <small class="text-muted">Versi baru tidak langsung dipakai. Aktifkan dari daftar versi setelah Anda yakin.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/certificate-template-editor.js') }}"></script>
@endpush

@extends('layouts.app-layout')

@php
    $isEdit = $badge->exists;
    $formAction = $isEdit ? route($routePrefix . '.badges.update', $badge) : route($routePrefix . '.badges.store');
    $pageTitle = $isEdit ? 'Ubah Badge' : 'Buat Badge Baru';
@endphp

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">{{ $pageTitle }}</h5>
                        <p class="m-b-0">Tentukan tampilan badge dan syarat yang harus dipenuhi siswa.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.badges.index') }}">Badge</a></li>
                        <li class="breadcrumb-item"><a href="#!">{{ $pageTitle }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Badge belum bisa disimpan:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" id="badge-form">
                        @csrf
                        @if ($isEdit) @method('PUT') @endif

                        <div class="card">
                            <div class="card-header"><h5>Tampilan Badge</h5></div>
                            <div class="card-block">
                                <div class="form-group row">
                                    <label for="title" class="col-sm-2 col-form-label">Judul Badge</label>
                                    <div class="col-sm-10">
                                        <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $badge->title) }}" required maxlength="255" placeholder="Contoh: Jago Kuis">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="description" class="col-sm-2 col-form-label">Deskripsi</label>
                                    <div class="col-sm-10">
                                        <textarea id="description" name="description" class="form-control" rows="3" required maxlength="1000" placeholder="Contoh: Diberikan kepada siswa yang sudah lulus 10 kuis.">{{ old('description', $badge->description) }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="icon" class="col-sm-2 col-form-label">Ikon Badge</label>
                                    <div class="col-sm-10">
                                        @if ($isEdit && $badge->icon_path)
                                            <img src="{{ asset('storage/' . $badge->icon_path) }}" alt="{{ $badge->title }}" style="width: 64px; height: 64px; object-fit: cover;" class="mb-2 d-block">
                                        @endif
                                        <input type="file" id="icon" name="icon" class="form-control" accept="image/png,image/jpeg,image/webp" {{ $isEdit ? '' : 'required' }}>
                                        <small class="form-text text-muted">
                                            Gambar PNG, JPG, atau WEBP, maksimal 1 MB. Sebaiknya persegi (rasio 1:1).
                                            @if ($isEdit) Kosongkan jika tidak ingin mengganti ikon. @endif
                                        </small>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Status</label>
                                    <div class="col-sm-10">
                                        <input type="hidden" name="is_active" value="0">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $badge->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">Aktif (badge ini bisa didapat siswa dan tampil di halaman Badge Saya)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><h5>Syarat mendapatkan badge</h5></div>
                            <div class="card-block">
                                <p class="text-muted">Siswa mendapat badge ini jika <strong>semua syarat</strong> di bawah terpenuhi (maksimal 3 syarat).</p>

                                @if ($locked)
                                    <div class="alert alert-warning">
                                        <i class="fa fa-lock"></i>
                                        <strong>Syarat dikunci</strong> karena sudah ada {{ $ownersCount }} siswa yang mendapat badge ini.
                                        Judul, deskripsi, ikon, dan status tetap bisa diubah. Untuk syarat yang berbeda, gunakan tombol <strong>Duplikat</strong> di daftar badge.
                                    </div>
                                    <ul class="list-group">
                                        @foreach ($badge->rules as $rule)
                                            <li class="list-group-item">{{ $rule->sentence() }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div id="rules-container"></div>
                                    <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="add-rule"><i class="fa fa-plus"></i> Tambah syarat</button>

                                    <div class="border-top pt-3">
                                        <button type="button" class="btn btn-secondary btn-sm" id="preview-count"><i class="fa fa-users"></i> Hitung siswa yang memenuhi</button>
                                        <span id="preview-result" class="ml-2 text-muted"></span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="text-right">
                            <a href="{{ route($routePrefix . '.badges.index') }}" class="btn btn-default">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Badge</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@unless ($locked)
@push('scripts')
<script>
    (function () {
        var catalog = {{ \Illuminate\Support\Js::from($catalog) }};
        var courses = {{ \Illuminate\Support\Js::from($courses) }};
        var categories = {{ \Illuminate\Support\Js::from($categories) }};
        var rules = {{ \Illuminate\Support\Js::from($initialRules) }};
        var maxRules = 3;
        var container = document.getElementById('rules-container');
        var addButton = document.getElementById('add-rule');
        var previewUrl = {{ \Illuminate\Support\Js::from(route($routePrefix . '.badges.preview-count')) }};
        var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '';
        var scopeLabels = { all: 'Semua kursus', course: 'Kursus tertentu', category: 'Kategori tertentu' };

        function el(tag, attrs, children) {
            var node = document.createElement(tag);
            Object.keys(attrs || {}).forEach(function (key) {
                if (key === 'text') { node.textContent = attrs[key]; } else { node.setAttribute(key, attrs[key]); }
            });
            (children || []).forEach(function (child) { node.appendChild(child); });
            return node;
        }

        function options(select, items, selected) {
            items.forEach(function (item) {
                var option = el('option', { value: item.value, text: item.label });
                if (String(item.value) === String(selected)) { option.selected = true; }
                select.appendChild(option);
            });
        }

        function renderRow(rule, index) {
            var row = el('div', { 'class': 'border rounded p-3 mb-2 rule-row' });
            var line = el('div', { 'class': 'form-row align-items-end' });

            var metric = el('select', { 'class': 'form-control js-metric', name: 'rules[' + index + '][metric]' });
            options(metric, Object.keys(catalog).map(function (key) { return { value: key, label: catalog[key].label }; }), rule.metric);

            var target = el('input', { 'class': 'form-control', type: 'number', min: 1, max: 100000, required: 'required', name: 'rules[' + index + '][target]', value: rule.target });
            var unit = el('span', { 'class': 'js-unit' });

            var scope = el('select', { 'class': 'form-control js-scope', name: 'rules[' + index + '][scope]' });
            var scopeId = el('select', { 'class': 'form-control js-scope-id', name: 'rules[' + index + '][scope_id]' });
            var remove = el('button', { type: 'button', 'class': 'btn btn-outline-danger btn-sm js-remove', text: 'Hapus syarat' });

            function fillScopes() {
                var allowed = catalog[metric.value].scopes;
                var current = allowed.indexOf(scope.value) >= 0 ? scope.value : (allowed.indexOf(rule.scope) >= 0 ? rule.scope : 'all');
                scope.innerHTML = '';
                options(scope, allowed.map(function (key) { return { value: key, label: scopeLabels[key] }; }), current);
                unit.textContent = catalog[metric.value].unit;
                fillScopeId();
            }

            function fillScopeId() {
                scopeId.innerHTML = '';
                if (scope.value === 'all') {
                    scopeId.style.display = 'none';
                    scopeId.disabled = true;
                    return;
                }
                var list = scope.value === 'course' ? courses : categories;
                var keep = rule.scope_id;
                scopeId.style.display = '';
                scopeId.disabled = false;
                options(scopeId, [{ value: '', label: scope.value === 'course' ? 'Pilih kursus...' : 'Pilih kategori...' }].concat(list.map(function (item) { return { value: item.id, label: item.name }; })), keep);
            }

            metric.addEventListener('change', function () { rule.scope_id = null; fillScopes(); });
            scope.addEventListener('change', function () { rule.scope_id = null; fillScopeId(); });
            remove.addEventListener('click', function () {
                rules = collect();
                rules.splice(index, 1);
                render();
            });

            function col(size, label, content) {
                return el('div', { 'class': 'form-group col-md-' + size }, [el('label', { text: label, 'class': 'small text-muted mb-1' }), content]);
            }

            line.appendChild(col(4, 'Siswa harus', metric));
            unit.className = 'ml-2 text-muted js-unit';
            var targetGroup = el('div', { 'class': 'd-flex align-items-center' }, [target, unit]);
            line.appendChild(col(3, 'Minimal', targetGroup));
            line.appendChild(col(2, 'Di', scope));
            line.appendChild(col(3, 'Pilihan', scopeId));
            row.appendChild(line);
            if (rules.length > 1) { row.appendChild(remove); }

            fillScopes();
            return row;
        }

        function collect() {
            return Array.prototype.map.call(container.querySelectorAll('.rule-row'), function (row) {
                return {
                    metric: row.querySelector('.js-metric').value,
                    target: row.querySelector('input[type=number]').value,
                    scope: row.querySelector('.js-scope').value,
                    scope_id: row.querySelector('.js-scope-id').disabled ? null : row.querySelector('.js-scope-id').value
                };
            });
        }

        function render() {
            container.innerHTML = '';
            rules.forEach(function (rule, index) { container.appendChild(renderRow(rule, index)); });
            addButton.style.display = rules.length >= maxRules ? 'none' : '';
        }

        addButton.addEventListener('click', function () {
            if (rules.length >= maxRules) { return; }
            rules = collect();
            rules.push({ metric: 'quiz_passed', target: 1, scope: 'all', scope_id: null });
            render();
        });

        document.getElementById('preview-count').addEventListener('click', function () {
            var result = document.getElementById('preview-result');
            result.textContent = 'Menghitung...';
            fetch(previewUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ rules: collect() })
            }).then(function (response) {
                return response.json().then(function (data) { return { ok: response.ok, data: data }; });
            }).then(function (res) {
                if (!res.ok) {
                    result.textContent = 'Lengkapi syarat dengan benar dulu (pilih kursus atau kategori, dan isi angka target).';
                    return;
                }
                var text = (res.data.capped ? 'Lebih dari ' : 'Saat ini ') + res.data.count + ' dari ' + res.data.total_students + ' siswa sudah memenuhi syarat ini.';
                result.textContent = text;
            }).catch(function () {
                result.textContent = 'Gagal menghitung. Coba lagi.';
            });
        });

        render();
    })();
</script>
@endpush
@endunless

@extends('layouts.app-layout')

@section('title', 'Kursus Saya')

@section('content')
@php
    $user = Auth::user();
    $tz = $user->timezone ?: session('user_timezone', config('app.timezone'));
    $statusPill = ['published' => 'rs-pill--green', 'pending_review' => 'rs-pill--amber', 'rejected' => 'rs-pill--red', 'draft' => 'rs-pill--gray', 'private' => 'rs-pill--gray'];
    $presentStatuses = $courses->pluck('status')->unique()->values();
    $openHash = request('open');
    $onlyOne = $courses->count() === 1;
@endphp
<div class="pcoded-content">
    <!-- Page-header start -->
    <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Kursus Saya', null]]">
        <x-slot:title>Kursus Saya</x-slot:title>
        <x-slot:subtitle>Kelola kursus dan modulnya di satu halaman.</x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
             <i class="bi bi-plus-lg" aria-hidden="true"></i> Buat Kursus Baru</a>
        </x-slot:actions>
    </x-dash.page-head>
    <!-- Page-header end -->

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if ($courses->isEmpty())
                        <div class="card">
                            <div class="card-block text-center py-4 text-muted">
                                <x-rs.illustration name="rs-12-instruktur-mulai" size="md" class="mb-3" />
                                <p class="mb-0">Anda belum membuat kursus.</p>
                            </div>
                        </div>
                    @else
                        <div class="rs-clist" id="course-list">
                            <div class="rs-clist__tools">
                                <label class="sr-only" for="course-search">Cari judul kursus</label>
                                <input type="search" id="course-search" class="form-control rs-clist__search" placeholder="Cari judul kursus..." autocomplete="off">
                                @if ($presentStatuses->count() > 1)
                                    <div class="rs-seg" role="radiogroup" aria-label="Status kursus">
                                        <input type="radio" name="course-status" id="cs-all" value="" checked><label for="cs-all">Semua</label>
                                        @foreach ($presentStatuses as $st)
                                            <input type="radio" name="course-status" id="cs-{{ $st }}" value="{{ $st }}"><label for="cs-{{ $st }}">{{ \App\Models\Course::STATUS_LABELS[$st] ?? $st }}</label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="rs-crow rs-crow--head" aria-hidden="true">
                                <span></span><span>Kursus</span><span>Kategori</span><span>Status</span><span>Siswa</span><span></span>
                            </div>

                            @foreach ($courses as $course)
                                @php
                                    $hash = $course->getRouteKey();
                                    $isOpen = $openHash === $hash || ($openHash === null && $onlyOne);
                                    $students = $course->students_count > 0 ? $course->students_count.' siswa' : 'Belum ada siswa';
                                    $updated = $course->updated_at->copy()->setTimezone($tz)->locale('id')->diffForHumans();
                                @endphp
                                <div class="rs-crow-wrap {{ $isOpen ? 'is-open' : '' }}" id="course-{{ $hash }}" data-status="{{ $course->status }}" data-title="{{ \Illuminate\Support\Str::lower($course->title) }}">
                                    <div class="rs-crow">
                                        <span class="rs-tile rs-crow__thumb {{ $course->thumbnail_url ? '' : 'rs-cover--c'.(($course->category_id ?? 0) % 5) }}" @if ($course->thumbnail_url) style="background-image: url('{{ Storage::url($course->thumbnail_url) }}'); background-size: cover" @endif aria-hidden="true"></span>
                                        <div class="rs-crow__main">
                                            <h3 class="rs-crow__title" id="ct-{{ $hash }}">{{ $course->title }}</h3>
                                            <small>{{ $course->lessons_count }} materi - diperbarui {{ $updated }}</small>
                                            <small class="rs-crow__meta">{{ $course->category?->name }}{{ $course->category ? ' - ' : '' }}{{ $course->status_label }} - {{ $students }}</small>
                                        </div>
                                        <span class="rs-crow__cat">{{ $course->category?->name }}</span>
                                        <span class="rs-crow__status"><span class="rs-pill {{ $statusPill[$course->status] ?? 'rs-pill--gray' }}">{{ $course->status_label }}</span></span>
                                        <span class="rs-crow__stu">{{ $students }}</span>
                                        <div class="rs-crow__acts">
                                            <button type="button" class="btn btn-primary rs-mod-toggle js-only" aria-expanded="{{ $isOpen ? 'true' : 'false' }}" aria-controls="mods-{{ $hash }}">
                                                <i class="bi bi-chevron-down" aria-hidden="true"></i> Modul <span class="rs-count">{{ $course->modules->count() }}</span><span class="sr-only"> untuk {{ $course->title }}</span>
                                            </button>
                                            <a href="{{ route('instructor.courses.modules.index', $course) }}" class="btn btn-primary no-js-only">
                                                <i class="bi bi-list-ul" aria-hidden="true"></i> Modul <span class="rs-count">{{ $course->modules->count() }}</span><span class="sr-only"> untuk {{ $course->title }}</span>
                                            </a>

                                            {{-- Dropdown Aksi Lainnya --}}
                                            <div class="dropdown">
                                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Aksi Lainnya">
                                                <i class="fa fa-cog"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                                <li><h6 class="dropdown-header">Kelola</h6></li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('student.courses.show', ['course' => $course->slug, 'preview' => 'true']) }}" target="_blank">
                                                        <i class="bi bi-eye text-primary me-2"></i> Pratinjau
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('instructor.courses.edit', $course) }}">
                                                        <i class="fa fa-pencil text-info me-2"></i> Edit Kursus
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('instructor.recap.index', $course) }}">
                                                        <i class="fa fa-file-text-o text-success me-2"></i> Rekap Nilai
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('instructor.course.monitoring.overview', $course) }}">
                                                        <i class="fa fa-shield text-info me-2"></i> Lap. Pelanggaran
                                                    </a>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item leaderboard-btn" type="button" data-url="{{ route('instructor.course.leaderboard', $course) }}">
                                                        <i class="fa fa-bar-chart text-warning me-2"></i> Data Student
                                                    </button>
                                                </li>
                        
                                                @if(in_array($course->status, ['draft', 'rejected']))
                                                <li><hr class="dropdown-divider"></li>
                                                <li><h6 class="dropdown-header">Status</h6></li>
                                                <li>
                                                    <form action="{{ route('instructor.courses.submit_review', $course) }}" method="POST" data-confirm="Ajukan kursus ini untuk direview?" data-confirm-ok="Ya, ajukan">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fa fa-paper-plane text-primary me-2"></i> Ajukan Review
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif

                                                @if(in_array($course->status, ['draft', 'published']))
                                                @if(!in_array($course->status, ['draft', 'rejected']))
                                                <li><hr class="dropdown-divider"></li>
                                                <li><h6 class="dropdown-header">Status</h6></li>
                                                @endif
                                                <li>
                                                    <form action="{{ route('instructor.courses.make_private', $course) }}" method="POST" data-confirm="Jadikan kursus ini privat?" data-confirm-ok="Ya, jadikan privat">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fa fa-lock text-secondary me-2"></i> Jadikan Privat
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif
                        
                                                <li><hr class="dropdown-divider"></li>
                                                <li><h6 class="dropdown-header">Lainnya</h6></li>
                                                <li>
                                                    <form action="{{ route('instructor.courses.clone', $course) }}" method="POST" data-confirm="Apakah Anda yakin ingin meng-clone kursus ini?" data-confirm-ok="Ya, clone">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fa fa-clone text-warning me-2"></i> Clone Kursus
                                                        </button>
                                                    </form>
                                                </li>
                        
                                                <li>
                                                    <form action="{{ route('instructor.courses.destroy', $course) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus kursus ini?" data-confirm-danger data-confirm-ok="Ya, hapus">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fa fa-trash text-danger me-2"></i> Hapus Kursus
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                        </div>
                                    </div>

                                    <div class="rs-mods" id="mods-{{ $hash }}" role="region" aria-labelledby="ct-{{ $hash }}" @unless ($isOpen) hidden @endunless>
                                        <div class="rs-mods__head"><b>Modul kursus ini</b><span>Geser untuk mengubah urutan</span></div>
                                        <div class="rs-mods__list" data-reorder-url="{{ route('instructor.courses.modules.reorder', $course) }}">
                                            @foreach ($course->modules as $module)
                                                <div class="rs-mrow" data-module-id="{{ $module->id }}">
                                                    <span class="rs-drag" title="Tahan dan geser untuk mengubah urutan"><i class="fa fa-bars" aria-hidden="true"></i></span>
                                                    <div class="rs-mrow__text">
                                                        <h4>{{ $module->title }}</h4>
                                                        <small>{{ $module->lessons_count > 0 ? $module->lessons_count.' materi' : 'Belum ada materi' }}</small>
                                                    </div>
                                                    <a href="{{ route('instructor.modules.lessons.index', $module) }}" class="btn btn-primary btn-sm" title="Kelola Pelajaran">
                                                        <i class="bi bi-pencil-square" aria-hidden="true"></i> Kelola Pelajaran<span class="sr-only">: {{ $module->title }}</span>
                                                    </a>
                                                    <div class="dropdown">
                                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Aksi Lainnya" data-bs-boundary="window">
                                                            <i class="fa fa-ellipsis-h" aria-hidden="true"></i><span class="sr-only">Aksi untuk {{ $module->title }}</span>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow">
                                                            <li>
                                                                <button class="dropdown-item leaderboard-btn" type="button" data-url="{{ route('instructor.module.leaderboard', $module) }}" data-modal-title="Papan Peringkat">
                                                                    <i class="fa fa-bar-chart text-warning me-2"></i> Peringkat Modul
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('instructor.modules.edit', $module) }}">
                                                                    <i class="fa fa-pencil text-info me-2"></i> Edit Modul
                                                                </a>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form action="{{ route('instructor.modules.destroy', $module) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus modul ini beserta seluruh pelajarannya?" data-confirm-danger data-confirm-ok="Ya, hapus">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit" class="dropdown-item text-danger">
                                                                        <i class="fa fa-trash text-danger me-2"></i> Hapus Modul
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if ($course->modules->isEmpty())
                                            <p class="rs-mods__empty">Belum ada modul di kursus ini.</p>
                                        @endif
                                        <a href="{{ route('instructor.courses.modules.create', $course) }}" class="rs-add-mod"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah modul</a>
                                    </div>
                                </div>
                            @endforeach

                            <div class="rs-clist__none" id="course-none" hidden>
                                <p>Tidak ada kursus yang cocok.</p>
                                <button type="button" class="btn btn-secondary" id="course-show-all">Tampilkan semua</button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Universal untuk Leaderboard (Data Student per kursus dan Peringkat Modul) -->
<div class="modal fade" id="leaderboardModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leaderboardModalTitle">Daftar Siswa</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="leaderboardModalContent">
                {{-- Konten leaderboard akan dimuat di sini --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<noscript><style>.js-only { display: none !important; } .no-js-only { display: inline-flex !important; }</style></noscript>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrf = '{{ csrf_token() }}';

    // ---- Module panels: open, close, and one Sortable per list, created when its panel first opens ----
    function initSortable(panel) {
        var list = panel.querySelector('.rs-mods__list');
        if (!list || list.dataset.sortable === '1' || typeof Sortable === 'undefined') return;
        list.dataset.sortable = '1';
        new Sortable(list, {
            handle: '.rs-drag',
            animation: 150,
            delay: 120,
            delayOnTouchOnly: true,
            // Pointer-based dragging instead of native drag and drop (unreliable in Chrome on Linux, no touch support).
            forceFallback: true,
            fallbackClass: 'sortable-fallback',
            fallbackTolerance: 3,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            onEnd: function () {
                var moduleIds = Array.from(list.children).map(function (child) { return child.dataset.moduleId; });
                fetch(list.dataset.reorderUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ module_ids: moduleIds })
                })
                .then(function (response) { return response.json(); })
                .then(function (data) { console.log(data.message || 'Reorder successful'); })
                .catch(function (error) { console.error('Error reordering modules:', error); });
            }
        });
    }

    document.querySelectorAll('.rs-mod-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var panel = document.getElementById(btn.getAttribute('aria-controls'));
            var open = btn.getAttribute('aria-expanded') !== 'true';
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            panel.hidden = !open;
            panel.closest('.rs-crow-wrap').classList.toggle('is-open', open);
            if (open) initSortable(panel);
        });
    });
    document.querySelectorAll('.rs-mods:not([hidden])').forEach(initSortable);

    // Open the panel named in the address (#course-xxx), if it is not open yet
    if (location.hash.indexOf('#course-') === 0) {
        var target = document.getElementById(location.hash.slice(1));
        if (target) target.scrollIntoView({ block: 'start' });
    }

    // ---- Dropdowns inside the list: lift the row above its neighbours so the menu is not cut off ----
    document.addEventListener('show.bs.dropdown', function (e) {
        var row = e.target.closest('.rs-crow-wrap, .rs-mrow');
        if (row) row.classList.add('is-dropdown-open');
    });
    document.addEventListener('hidden.bs.dropdown', function (e) {
        var row = e.target.closest('.rs-crow-wrap, .rs-mrow');
        if (row) row.classList.remove('is-dropdown-open');
    });

    // ---- Search and status filter (in the browser only) ----
    var search = document.getElementById('course-search');
    var rows = Array.from(document.querySelectorAll('.rs-crow-wrap'));
    var none = document.getElementById('course-none');
    function applyFilter() {
        var q = (search.value || '').trim().toLowerCase();
        var checked = document.querySelector('input[name="course-status"]:checked');
        var status = checked ? checked.value : '';
        var shown = 0;
        rows.forEach(function (row) {
            var ok = (!q || row.dataset.title.indexOf(q) !== -1) && (!status || row.dataset.status === status);
            row.hidden = !ok;
            if (ok) shown++;
        });
        if (none) none.hidden = shown !== 0;
    }
    if (search) {
        search.addEventListener('input', applyFilter);
        document.querySelectorAll('input[name="course-status"]').forEach(function (r) { r.addEventListener('change', applyFilter); });
        var showAll = document.getElementById('course-show-all');
        if (showAll) showAll.addEventListener('click', function () {
            search.value = '';
            var all = document.getElementById('cs-all');
            if (all) all.checked = true;
            applyFilter();
            search.focus();
        });
    }

    // ---- Leaderboard modal (course "Data Student" and module "Peringkat Modul") ----
    var modalEl = document.getElementById('leaderboardModal');
    var modalContent = document.getElementById('leaderboardModalContent');
    var modalTitle = document.getElementById('leaderboardModalTitle');
    var leaderboardModal = new bootstrap.Modal(modalEl);
    document.querySelectorAll('.leaderboard-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            modalTitle.textContent = this.dataset.modalTitle || 'Daftar Siswa';
            modalContent.innerHTML = '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i></div>';
            leaderboardModal.show();
            fetch(this.dataset.url)
                .then(function (response) { return response.json(); })
                .then(function (data) { modalContent.innerHTML = data.html; })
                .catch(function (error) {
                    console.error('Error:', error);
                    modalContent.innerHTML = '<p class="text-danger">Gagal memuat data.</p>';
                });
        });
    });
});
</script>
@endpush

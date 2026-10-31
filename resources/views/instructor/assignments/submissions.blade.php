@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Pengumpulan Tugas</h5>
                        <p class="m-b-0">Tugas: <strong>{{ $assignment->lesson->title }}</strong></p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $assignment->lesson->module) }}">Daftar Pelajaran</a></li>
                        <li class="breadcrumb-item"><a href="#!">Pengumpulan</a></li>
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
                                    <h5>Daftar Pengumpulan Siswa</h5>
                                    <span>Berikut adalah daftar semua siswa baik yang telah mengumpulkan tugas ini atau belum.</span>
                                </div>
                                <div class="card-block">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs md-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab" href="#submitted" role="tab">Menunggu Dinilai <span class="badge badge-info">{{ $submittedSubmissions->count() }}</span></a>
                                            <div class="slide"></div>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#revision" role="tab">Perlu Revisi <span class="badge badge-danger">{{ $revisionSubmissions->count() }}</span></a>
                                            <div class="slide"></div>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#passed" role="tab">Lulus <span class="badge badge-success">{{ $passedSubmissions->count() }}</span></a>
                                            <div class="slide"></div>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#not-submitted" role="tab">Belum Mengumpulkan <span class="badge badge-default">{{ $notSubmittedStudents->count() }}</span></a>
                                            <div class="slide"></div>
                                        </li>
                                    </ul>
                                    <!-- Tab panes -->
                                    <div class="tab-content card-block">
                                        {{-- TAB: Menunggu Dinilai --}}
                                        <div class="tab-pane active" id="submitted" role="tabpanel">
                                            @include('instructor.assignments.partials._submission_table', [
                                                'submissions' => $submittedSubmissions, 
                                                'showGrade' => false,
                                                'allowBulkRevision' => true,
                                                'tabId' => 'submitted'
                                            ])
                                        </div>
                                        {{-- TAB: Perlu Revisi --}}
                                        <div class="tab-pane" id="revision" role="tabpanel">
                                            @include('instructor.assignments.partials._submission_table', [
                                                'submissions' => $revisionSubmissions, 
                                                'showGrade' => true,
                                                'allowBulkRevision' => false,
                                                'tabId' => 'revision'
                                            ])
                                        </div>
                                        {{-- TAB: Lulus --}}
                                        <div class="tab-pane" id="passed" role="tabpanel">
                                            @include('instructor.assignments.partials._submission_table', [
                                                'submissions' => $passedSubmissions, 
                                                'showGrade' => true,
                                                'allowBulkRevision' => true,
                                                'tabId' => 'passed'
                                            ])
                                        </div>
                                        {{-- TAB: Belum Mengumpulkan --}}
                                        <div class="tab-pane" id="not-submitted" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-center" >NIM/NIDN/NIP</th>
                                                            <th>Nama Siswa</th>
                                                            <th>Email</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($notSubmittedStudents as $student)
                                                            <tr>
                                                                <td class="text-center">{{ $student->studentProfile->unique_id_number ? $student->studentProfile->unique_id_number : '-' }}</td>
                                                                <td>{{ $student->name }}</td>
                                                                <td>{{ $student->email }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="2" class="text-center">Semua siswa sudah mengumpulkan tugas.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk setiap pengumpulan -->
    @foreach ($submittedSubmissions->concat($revisionSubmissions)->concat($passedSubmissions) as $submission)
    <div class="modal fade" id="submissionModal-{{ $submission->id }}" tabindex="-1" role="dialog">
<div class="modal-dialog modal-pdf-viewer" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pengumpulan: {{ $submission->user->studentProfile?->unique_id_number ? $submission->user->studentProfile->unique_id_number . ' - ' : '' }}{{ $submission->user->name }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-9">
                            <h6 class="font-weight-bold">Pratinjau File</h6>
                            @if(Str::endsWith($submission->file_path, '.pdf'))
                                <div class="rs-doc-viewer position-relative">
                                    <div class="pdf-loading-indicator text-center p-4 text-muted" style="position: absolute; top: 40%; left: 0; right: 0; display: none;">
                                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                                        <p class="mt-2 mb-0">Memuat pratinjau dokumen...</p>
                                    </div>
                                    <iframe data-src="{{ route('files.submission', $submission) }}" 
                                            src="about:blank" 
                                            frameborder="0"
                                            style="display: block; position: relative; z-index: 1;">
                                        This browser does not support PDFs. Silakan unduh file untuk melihatnya.
                                    </iframe>
                                </div>
                                <a href="{{ route('files.submission', $submission) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-block mt-3">Buka di tab baru</a>
                            @else
                                <div class="text-center p-5 bg-light">
                                    <i class="fa fa-file-zip-o fa-3x"></i>
                                    <p class="mt-2">Pratinjau tidak tersedia untuk file ZIP.</p>
                                </div>
                            @endif
                            <a href="{{ route('files.submission', ['submission' => $submission, 'download' => 1]) }}" class="btn btn-secondary btn-block mt-3">
                                <i class="fa fa-download"></i> Unduh File Tugas
                            </a>
                        </div>
                        <div class="col-lg-3">
                            <h6 class="font-weight-bold">Form Penilaian</h6>
                            {{-- Tambahkan peringatan keterlambatan di sini --}}
                            @if ($submission->is_late)
                                <div class="alert alert-warning bg-warning text-dark" role="alert">
                                    <i class="fa fa-exclamation-triangle"></i> 
                                    <strong>Perhatian:</strong> Siswa ini mengumpulkan tugas melewati batas waktu.
                                </div>
                            @endif
                            <form action="{{ route('instructor.submission.grade', $submission) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="grade-{{ $submission->id }}">Nilai (0-100)</label>
                                    <input type="number" name="grade" id="grade-{{ $submission->id }}" class="form-control" style="max-width: 140px;" value="{{ old('grade', $submission->grade) }}" min="0" max="100" required>
                                </div>
                                <div class="form-group">
                                    <label for="feedback-{{ $submission->id }}">Umpan Balik (Feedback)</label>
                                    <textarea name="feedback" id="feedback-{{ $submission->id }}" class="form-control" rows="8" style="width: 100%" placeholder="Berikan umpan balik untuk siswa...">{{ old('feedback', $submission->feedback) }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Simpan Penilaian</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Modal Konfirmasi Revisi Masal (Menunggu Dinilai) -->
<div class="modal fade" id="bulkReviseModal-submitted" tabindex="-1" role="dialog" aria-labelledby="bulkReviseModalLabel-submitted" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('instructor.assignment.submissions.bulk_revision', $assignment) }}" method="POST" id="form-bulk-revise-submitted">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkReviseModalLabel-submitted">
                        <i class="fa fa-exclamation-triangle text-warning"></i> Konfirmasi Revisi Masal
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        Anda akan meminta revisi untuk <strong class="modal-selected-count-submitted">0</strong> tugas siswa terpilih.
                    </div>
                    <p class="text-muted small">
                        Status tugas yang dipilih akan diubah menjadi <strong>Perlu Revisi</strong>, nilai diset ke <strong>0</strong>, tanda selesai pelajaran siswa akan dicabut, dan siswa akan menerima email notifikasi revisi.
                    </p>
                    <div class="form-group">
                        <label for="bulk-feedback-submitted" class="font-weight-bold">Catatan / Umpan Balik Revisi:</label>
                        <textarea name="feedback" id="bulk-feedback-submitted" class="form-control" rows="4" placeholder="Tuliskan catatan umpan balik revisi..." required>Tugas belum sesuai kriteria. Silakan perbaiki dan kumpulkan kembali.</textarea>
                    </div>
                    {{-- Container dinamis input hidden submission_ids[] --}}
                    <div class="hidden-submission-ids-container-submitted"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btn-submit-bulk-revise-submitted">
                        <i class="fa fa-undo"></i> Ya, Minta Revisi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Revisi Masal (Lulus) -->
<div class="modal fade" id="bulkReviseModal-passed" tabindex="-1" role="dialog" aria-labelledby="bulkReviseModalLabel-passed" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('instructor.assignment.submissions.bulk_revision', $assignment) }}" method="POST" id="form-bulk-revise-passed">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkReviseModalLabel-passed">
                        <i class="fa fa-exclamation-triangle text-warning"></i> Konfirmasi Revisi Masal
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        Anda akan meminta revisi untuk <strong class="modal-selected-count-passed">0</strong> tugas siswa terpilih.
                    </div>
                    <p class="text-muted small">
                        Status tugas yang dipilih akan diubah menjadi <strong>Perlu Revisi</strong>, nilai diset ke <strong>0</strong>, tanda selesai pelajaran siswa akan dicabut, dan siswa akan menerima email notifikasi revisi.
                    </p>
                    <div class="form-group">
                        <label for="bulk-feedback-passed" class="font-weight-bold">Catatan / Umpan Balik Revisi:</label>
                        <textarea name="feedback" id="bulk-feedback-passed" class="form-control" rows="4" placeholder="Tuliskan catatan umpan balik revisi..." required>Tugas belum sesuai kriteria. Silakan perbaiki dan kumpulkan kembali.</textarea>
                    </div>
                    {{-- Container dinamis input hidden submission_ids[] --}}
                    <div class="hidden-submission-ids-container-passed"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btn-submit-bulk-revise-passed">
                        <i class="fa fa-undo"></i> Ya, Minta Revisi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal-pdf-viewer {
    max-width: calc(100vw - 2rem);
    margin: 1rem auto;
}

@media (max-width: 768px) {
    .modal-pdf-viewer {
        max-width: 98%;
        margin: 0.5rem auto;
    }
}

@media (max-width: 576px) {
    .modal-pdf-viewer {
        max-width: 100%;
        margin: 0;
    }
}
</style>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Pindahkan semua modal ke direct child dari body agar tidak terkurung oleh stacking context / transform container
    document.querySelectorAll('.modal').forEach(function (m) {
        if (m.parentElement !== document.body) {
            document.body.appendChild(m);
        }
    });

    // Lazy load PDF iframe hanya saat modal dibuka, dan bersihkan saat ditutup untuk menghemat resource
    function loadPdfIframe(modal) {
        if (!modal) return;
        const iframe = modal.querySelector('iframe[data-src]');
        const loader = modal.querySelector('.pdf-loading-indicator');
        if (iframe && (!iframe.getAttribute('src') || iframe.getAttribute('src') === 'about:blank')) {
            if (loader) {
                loader.style.display = 'block';
            }
            iframe.onload = function () {
                if (loader && iframe.getAttribute('src') !== 'about:blank') {
                    loader.style.display = 'none';
                }
            };
            iframe.src = iframe.getAttribute('data-src');
        }
    }

    function unloadPdfIframe(modal) {
        if (!modal) return;
        const iframe = modal.querySelector('iframe[data-src]');
        const loader = modal.querySelector('.pdf-loading-indicator');
        if (iframe) {
            iframe.src = 'about:blank';
            if (loader) {
                loader.style.display = 'none';
            }
        }
    }

    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('show.bs.modal', '.modal', function () {
            loadPdfIframe(this);
        });
        jQuery(document).on('hidden.bs.modal', '.modal', function () {
            unloadPdfIframe(this);
        });
    }

    document.addEventListener('show.bs.modal', function (event) {
        loadPdfIframe(event.target);
    });
    document.addEventListener('hidden.bs.modal', function (event) {
        unloadPdfIframe(event.target);
    });

    ['submitted', 'passed'].forEach(function (tabId) {
        const selectAllCb = document.getElementById('select-all-' + tabId);
        const bulkBtn = document.getElementById('btn-bulk-revise-' + tabId);
        const countSpans = document.querySelectorAll('.selected-count-' + tabId);
        const infoSpan = document.getElementById('selection-info-' + tabId);
        const modal = document.getElementById('bulkReviseModal-' + tabId);
        const form = document.getElementById('form-bulk-revise-' + tabId);
        const submitBtn = document.getElementById('btn-submit-bulk-revise-' + tabId);

        if (!bulkBtn) return;

        function updateSelectionState() {
            const checkedBoxes = document.querySelectorAll('.submission-checkbox-' + tabId + ':checked');
            const totalBoxes = document.querySelectorAll('.submission-checkbox-' + tabId);
            const count = checkedBoxes.length;

            countSpans.forEach(function (span) {
                span.textContent = count;
            });

            if (infoSpan) {
                infoSpan.style.display = count > 0 ? 'inline' : 'none';
            }

            bulkBtn.disabled = count === 0;

            if (selectAllCb && totalBoxes.length > 0) {
                selectAllCb.checked = count === totalBoxes.length;
                selectAllCb.indeterminate = count > 0 && count < totalBoxes.length;
            }
        }

        if (selectAllCb) {
            selectAllCb.addEventListener('change', function () {
                const isChecked = this.checked;
                const checkboxes = document.querySelectorAll('.submission-checkbox-' + tabId);
                checkboxes.forEach(function (cb) {
                    cb.checked = isChecked;
                });
                updateSelectionState();
            });
        }

        const itemCbs = document.querySelectorAll('.submission-checkbox-' + tabId);
        itemCbs.forEach(function (cb) {
            cb.addEventListener('change', function () {
                updateSelectionState();
            });
        });

        // Event saat modal dibuka: masukkan hidden inputs submission_ids[] ke form modal
        if (modal) {
            $(modal).on('show.bs.modal', function () {
                const checkedBoxes = document.querySelectorAll('.submission-checkbox-' + tabId + ':checked');
                const modalCountSpan = modal.querySelector('.modal-selected-count-' + tabId);
                const hiddenContainer = modal.querySelector('.hidden-submission-ids-container-' + tabId);

                if (modalCountSpan) {
                    modalCountSpan.textContent = checkedBoxes.length;
                }

                if (hiddenContainer) {
                    hiddenContainer.innerHTML = '';
                    checkedBoxes.forEach(function (cb) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'submission_ids[]';
                        input.value = cb.value;
                        hiddenContainer.appendChild(input);
                    });
                }
            });
        }

        // Proteksi saat form dikirim
        if (form && submitBtn) {
            form.addEventListener('submit', function (e) {
                const hiddenInputs = form.querySelectorAll('input[name="submission_ids[]"]');
                if (hiddenInputs.length === 0) {
                    e.preventDefault();
                    alert('Silakan pilih setidaknya satu tugas siswa terlebih dahulu.');
                    return false;
                }
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...';
            });
        }
    });
});
</script>
@endpush



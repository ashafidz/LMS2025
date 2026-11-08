@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <x-dash.page-head :crumbs="[['__home', route('student.dashboard')], ['Sertifikat', null]]">
        <x-slot:title>Sertifikat Saya</x-slot:title>
        <x-slot:subtitle>Daftar sertifikat kursus milik {{ Auth::user()->name }}</x-slot:subtitle>
    </x-dash.page-head>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    @if (session('error'))
                        <div class="alert alert-danger mb-4">
                            <i class="fa fa-exclamation-circle mr-2"></i> {{ session('error') }}
                        </div>
                    @endif
                    @php
                        $tz = Auth::user()->timezone ?: config('app.timezone');
                    @endphp
                    @if ($certificateCount > 0)
                        <section class="rs-cert-hero" aria-labelledby="cert-hero-title">
                            <x-rs.illustration name="rs-15-sertifikat-terbit" size="sm" class="rs-cert-hero__img" />
                            <div>
                                <h2 id="cert-hero-title">Selamat! Kamu sudah meraih {{ $certificateCount }} sertifikat.</h2>
                                <p>Unduh sertifikat dalam bentuk PDF kapan saja, untuk dicetak atau dibagikan.</p>
                            </div>
                        </section>
                    @endif

                    @if ($certificateCount > 0)
                        <x-dash.list-toolbar placeholder="Cari judul kursus..." label="Cari sertifikat" />
                    @endif

                    @if ($certificates->isEmpty() && request()->filled('search'))
                        <p class="text-muted text-center py-4">Tidak ada sertifikat yang cocok dengan pencarian.</p>
                    @elseif ($certificates->isEmpty())
                        <div class="card">
                            <div class="card-block text-center py-5">
                                <div class="mb-3">
                                    <x-rs.illustration name="rs-09-sertifikat-kosong" size="md" />
                                </div>
                                <h2 class="h5 mb-2">Belum ada sertifikat</h2>
                                <p class="text-muted mb-4">Selesaikan kursus Anda untuk mendapatkan sertifikat yang bisa diunduh dan dibagikan.</p>
                                <a href="{{ route('courses') }}" class="btn btn-secondary">
                                    Jelajahi kursus <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="rs-certs">
                            @foreach ($certificates as $certificate)
                                @php $course = $certificate->course; @endphp
                                <article class="rs-cert">
                                    <div class="rs-cert__cover {{ $course?->thumbnail_url ? 'has-img' : 'rs-cover--c'.(($course->category_id ?? 0) % 5) }}"
                                        @if ($course?->thumbnail_url) style="background-image: url('{{ Storage::url($course->thumbnail_url) }}')" @endif aria-hidden="true">
                                        <span class="rs-cert__seal"><i class="bi bi-award-fill"></i></span>
                                    </div>
                                    <div class="rs-cert__body">
                                        <span class="rs-cert__eyebrow">Sertifikat kelulusan</span>
                                        <h3 class="rs-cert__title">{{ $course?->title ?? 'Kursus tidak tersedia' }}</h3>
                                        <dl class="rs-cert__meta">
                                            <div><dt>Terbit</dt><dd>{{ $certificate->issued_at->copy()->setTimezone($tz)->locale('id')->isoFormat('D MMMM YYYY') }}</dd></div>
                                            <div><dt>Kode</dt><dd class="rs-cert__code">{{ $certificate->certificate_code }}</dd></div>
                                        </dl>
                                        @if ($certificate->has_incomplete_lessons ?? false)
                                            <div class="rs-cert__note" role="note">
                                                <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                                                <span><strong>Kursus Diperbarui:</strong> ada pelajaran baru ({{ $certificate->completed_lessons_count }}/{{ $certificate->total_lessons_count }} selesai). Selesaikan semua pelajaran untuk mengunduh sertifikat.</span>
                                            </div>
                                            <a href="{{ route('student.courses.show', $course) }}" class="btn btn-secondary btn-block mt-auto">
                                                <i class="bi bi-book" aria-hidden="true"></i> Lanjutkan Kursus
                                            </a>
                                        @elseif ($course)
                                            <a href="{{ route('student.certificate.download', $certificate->course_id) }}" class="btn btn-primary btn-block mt-auto download-cert-btn">
                                                <i class="bi bi-download" aria-hidden="true"></i> Unduh Sertifikat
                                            </a>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif

                    @if ($certificates->total() > 0)
                        <div class="card rs-pager-card"><div class="card-block"><x-dash.pagination :items="$certificates" :per-page-options="[10, 25, 50]" /></div></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Loading Animation Modal -->
    <div id="loadingModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="loadingLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <h5 id="loadingLabel" class="mt-3">Mengunduh Sertifikat...</h5>
                    <p class="text-muted">Harap tunggu, sertifikat Anda sedang diproses.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Loading Modal Styles */
    .modal.show {
        background-color: rgba(0, 0, 0, 0.5);
    }

    .spinner-border {
        display: inline-block;
        width: 3rem;
        height: 3rem;
        vertical-align: text-bottom;
        animation: spinner-border 0.75s linear infinite;
    }

    @keyframes spinner-border {
        to {
            transform: rotate(360deg);
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const downloadButtons = document.querySelectorAll('.download-cert-btn');
    const loadingModal = document.getElementById('loadingModal');
    const bsModal = new bootstrap.Modal(loadingModal);

    downloadButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            // Show loading modal
            bsModal.show();

            // Auto-hide modal after 8 seconds (PDF generation usually takes 3-5 seconds)
            setTimeout(function () {
                bsModal.hide();
            }, 8000);

            // Optional: hide modal when download starts (visual feedback)
            // This requires additional server-side implementation for better UX
        });
    });
});
</script>
@endpush
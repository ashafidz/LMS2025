@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Rekap Nilai Kursus</h5>
                        <p class="m-b-0" style="font-size: 2rem;"><strong>{{ $course->title }}</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">
                        <div class="col-lg-4 order-lg-2">
                            <div class="card sticky-top" style="top: 80px;">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="feather icon-layers me-2"></i>Daftar Modul</h5>
                                </div>
                                <div class="module-list-container">
                                    @forelse($course->modules as $module)
                                        <a href="#" class="module-item" data-url="{{ route('instructor.recap.module_data', $module->id) }}">
                                            <i class="feather icon-book-open module-icon"></i>
                                            <span class="module-title">{{ $module->title }}</span>
                                            <i class="feather icon-chevron-right arrow-icon"></i>
                                        </a>
                                    @empty
                                        <div class="text-center p-4 text-muted">
                                            <p class="mb-0">Belum ada modul yang ditambahkan.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8 order-lg-1">
                            <div id="recap-content-wrapper">
                                {{-- Placeholder Awal --}}
                                <div class="card placeholder-card">
                                    <div class="card-body text-center">
                                        
                                        <h4 class="mt-3">Mulai Menganalisis</h4>
                                        <p class="text-muted">Pilih salah satu modul dari daftar di sebelah kanan untuk menampilkan rekapitulasi nilai mahasiswa.</p>
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
@endsection

@push('styles')
{{-- Style untuk tampilan yang lebih modern --}}
<style>
    :root {
        --primary-color: #4680FF;
        --light-primary: #e9efff;
        --border-color: #e0e6ed;
        --card-shadow: 0 4px 24px 0 rgba(34, 41, 47, 0.1);
    }
    .card {
        border: none;
        border-radius: 4px;
        box-shadow: var(--card-shadow);
        margin-bottom: 25px;
    }
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid var(--border-color);
        padding: 1rem 1.25rem;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }
    .module-list-container {
        padding: 0.5rem;
        max-height: 70vh;
        overflow-y: auto;
    }
    .module-item {
        display: flex;
        align-items: center;
        padding: 0.9rem 1rem;
        border-radius: 4px;
        text-decoration: none;
        color: #333;
        font-weight: 500;
        margin-bottom: 0.25rem;
        transition: all 0.2s ease-in-out;
        border: 1px solid transparent;
    }
    .module-item:hover {
        background-color: var(--light-primary);
        color: var(--primary-color);
    }
    .module-item.active {
        background-color: var(--light-primary);
        color: var(--primary-color);
        font-weight: 600;
        box-shadow: 0 0 0 2px var(--primary-color);
    }
    .module-icon {
        margin-right: 12px;
        font-size: 1.2rem;
    }
    .module-title {
        flex-grow: 1;
    }
    .arrow-icon {
        transition: transform 0.2s ease;
    }
    .module-item.active .arrow-icon {
        transform: translateX(3px);
    }
    .placeholder-card {
        border: 2px dashed var(--border-color);
        background-color: #fbfcff;
        box-shadow: none;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 400px;
    }
    .placeholder-card img {
        width: 120px;
        opacity: 0.6;
    }

    /* Skeleton Loader untuk efek loading yang lebih baik */
    .skeleton-card {
        background-color: #fff;
        padding: 1.5rem;
    }
    .skeleton {
        animation: skeleton-loading 1s linear infinite alternate;
        opacity: 0.7;
        border-radius: 4px;
    }
    @keyframes skeleton-loading {
        0% { background-color: hsl(200, 20%, 80%); }
        100% { background-color: hsl(200, 20%, 95%); }
    }
    .skeleton-header { width: 40%; height: 28px; margin-bottom: 2rem; }
    .skeleton-row { display: flex; gap: 10px; margin-bottom: 10px; }
    .skeleton-item { height: 20px; flex-grow: 1; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const moduleLinks = document.querySelectorAll('.module-item');
    const recapContainer = document.getElementById('recap-content-wrapper');

    // HTML untuk Skeleton Loader
    const skeletonLoaderHTML = `
        <div class="card skeleton-card">
            <div class="skeleton skeleton-header"></div>
            <div class="skeleton-row"><div class="skeleton skeleton-item"></div><div class="skeleton skeleton-item"></div><div class="skeleton skeleton-item"></div></div>
            <div class="skeleton-row"><div class="skeleton skeleton-item"></div><div class="skeleton skeleton-item"></div><div class="skeleton skeleton-item"></div></div>
            <div class="skeleton-row"><div class="skeleton skeleton-item"></div><div class="skeleton skeleton-item"></div><div class="skeleton skeleton-item"></div></div>
            <div class="skeleton-row"><div class="skeleton skeleton-item"></div><div class="skeleton skeleton-item"></div><div class="skeleton skeleton-item"></div></div>
        </div>
    `;

    // HTML untuk pesan error
    const errorHTML = `
        <div class="card">
            <div class="card-body text-center text-danger p-5">
                <i class="feather icon-alert-triangle fa-3x mb-3"></i>
                <h5>Terjadi Kesalahan</h5>
                <p>Gagal memuat data rekapitulasi. Silakan coba lagi atau hubungi administrator.</p>
            </div>
        </div>
    `;

    moduleLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            // Hapus kelas 'active' dari semua link dan tambahkan ke yang diklik
            moduleLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');

            const url = this.dataset.url;
            
            // Tampilkan skeleton loader
            recapContainer.innerHTML = skeletonLoaderHTML;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Ganti skeleton dengan konten asli setelah 300ms untuk UX
                    setTimeout(() => {
                        recapContainer.innerHTML = data.html;
                    }, 300);
                })
                .catch(error => {
                    console.error('Error fetching recap data:', error);
                    recapContainer.innerHTML = errorHTML;
                });
        });
    });
});
</script>
@endpush
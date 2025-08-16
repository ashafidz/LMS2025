{{-- resources/views/student/courses/partials/_certificate_preview.blade.php --}}

<style>
    .certificate-preview-wrapper {
        position: relative;
        width: 100%;
        max-width: 800px; /* Batas lebar maksimum */
        margin: 0 auto;
        border: 1px solid #ddd;
        aspect-ratio: 297 / 210; /* Rasio A4 Landscape */
    }
    .certificate-preview-wrapper .background-image {
        width: 100%;
        height: 100%;
        display: block;
    }
    .certificate-preview-wrapper .text-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        font-family: 'Helvetica', sans-serif;
    }
    /* Penempatan Teks (menggunakan persentase agar responsif) */
    .cert-platform-name { 
        position: absolute; 
        top: 12%; 
        left: 7%; 
        font-size: 1.8vw; 
    }
    .cert-code { 
        position: absolute; 
        top: 12%; 
        right: 7%; 
        font-size: 1.4vw; 
        color: #555; 
    }
    .cert-completion-text { 
        position: absolute; 
        top: 26%; 
        left: 7%; 
        font-size: 1.2vw; 
        letter-spacing: 1px; 
    }
    .cert-course-title { 
        position: absolute; 
        top: 30%; 
        left: 7%; 
        font-size: 3.5vw; 
        font-weight: bold; 
        max-width: 70%; 
        line-height: 1.2; 
    }
    .cert-instructor { 
        position: absolute; 
        top: 45%; 
        left: 7%; 
        font-size: 1.4vw; 
        color: #555; 
    }
    .cert-student-name { 
        position: absolute; 
        top: 62%; 
        left: 7%; 
        font-size: 4vw; 
        font-weight: bold; 
    }
    .cert-date { 
        position: absolute; 
        top: 72%; left: 7%; 
        font-size: 1.4vw; 
        color: #555; 
        }
</style>

{{-- Pratinjau Visual Sertifikat --}}
{{-- <div class="certificate-preview-wrapper mb-4">
    <img src="{{ asset('assets/images/certificate-background.png') }}" class="background-image" alt="Latar Sertifikat">
    
    <div class="text-overlay">
        @php $settings = \App\Models\SiteSetting::first(); @endphp
        <div class="cert-platform-name">
            @if($settings->logo_path)
                <img src="{{ Storage::url($settings->logo_path) }}" alt="Logo" style="height: 2.5vw;">
            @else
                <strong>{{ $settings->site_name }}</strong>
            @endif
        </div>
        <div class="cert-code">{{ $certificate->certificate_code }}</div>
        <div class="cert-completion-text">CERTIFICATE OF COMPLETION</div>
        <div class="cert-course-title">{{ $course->title }}</div>
        <div class="cert-instructor">Instructors <strong>{{ $course->instructor->name }}</strong></div>
        <div class="cert-student-name">{{ $user->name }}</div>
        <div class="cert-date">Date <strong>{{ $certificate->issued_at->format('d F Y') }}</strong></div>
    </div>
</div> --}}

{{-- Konten Lama (sekarang di bawah pratinjau) --}}
<div class="certificate-info text-center p-4">
        <i class="fa fa-trophy text-warning" style="font-size: 5rem;"></i>
    <h3 class="mt-3 font-weight-bold">Sertifikat Kelulusan Siap Diunduh!</h3>
    <p class="text-muted">Selamat, {{ $user->name }}! Anda telah berhasil menyelesaikan kursus "{{ $course->title }}".</p>
    <hr class="my-4">
    <p>Unduh sertifikat kelulusan Anda sebagai bukti pencapaian ini. Bagikan di profil profesional Anda untuk menunjukkan keahlian baru Anda!</p>
    <div class="mt-4">
        <a href="{{ route('student.certificate.download', $course->id) }}" class="btn btn-primary btn-lg">
            <i class="fa fa-download"></i> Unduh Sertifikat (PDF)
        </a>
    </div>
</div>
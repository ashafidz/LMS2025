{{-- resources/views/student/courses/partials/_certificate_preview.blade.php --}}

<div class="certificate-preview text-center p-4">
    <i class="fa fa-trophy text-warning" style="font-size: 5rem;"></i>
    <h3 class="mt-3 font-weight-bold">Sertifikat Kelulusan</h3>
    <p class="text-muted">Selamat, {{ $user->name }}! Anda telah berhasil menyelesaikan kursus:</p>
    <h4 class="font-weight-bold">{{ $course->title }}</h4>

    <hr class="my-4">

    <p>Anda sekarang berhak untuk mengunduh sertifikat kelulusan Anda sebagai bukti pencapaian ini. Bagikan di profil profesional Anda untuk menunjukkan keahlian baru Anda!</p>

    <div class="mt-4">
        <a href="{{ route('student.certificate.download', $course->id) }}" class="btn btn-primary btn-lg">
            <i class="fa fa-download"></i> Unduh Sertifikat (PDF)
        </a>
    </div>
</div>
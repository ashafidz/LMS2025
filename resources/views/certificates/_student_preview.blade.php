{{-- Modal sertifikat siswa saat ada template aktif: tampilan visual + tombol unduh --}}
<div class="certificate-info text-center p-3">
    <div style="max-width: 800px; margin: 0 auto;">
        {!! $canvas !!}
    </div>
    <h4 class="mt-4 font-weight-bold">Sertifikat Kelulusan Siap Diunduh!</h4>
    <p class="text-muted">Selamat, {{ $user->name }}! Anda telah berhasil menyelesaikan kursus "{{ $course->title }}".</p>
    <div class="mt-3">
        <a href="{{ route('student.certificate.download', $course) }}" class="btn btn-primary btn-lg">
            <i class="fa fa-download"></i> Unduh Sertifikat (PDF)
        </a>
    </div>
</div>

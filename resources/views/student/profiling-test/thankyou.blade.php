@extends('layouts.app-layout')

@section('content')
<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fa fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    
                    <h2 class="mb-3 text-success">Terima kasih telah mengerjakan profiling test ini!</h2>
                    
                    <p class="lead mb-4 text-muted">
                        Data Anda sedang diproses oleh sistem untuk menyesuaikan rute pembelajaran dan konten kursus yang paling sesuai dengan gaya belajar Anda.
                    </p>

                    <div class="alert alert-warning mb-4 text-left">
                        <strong><i class="fa fa-info-circle mr-1"></i> Informasi:</strong> Saat ini, sistem sedang mencari profil pembelajaran yang paling cocok untuk Anda (Proses K-Means Clustering). 
                        Silakan tunggu sejenak atau kembali ke dashboard sambil menunggu proses selesai.
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('student.courses.show', $course->slug) }}" class="btn btn-primary btn-lg px-5 mb-2">Masuk ke Kursus</a>
                        <br>
                        <a href="{{ route('student.dashboard') }}" class="btn btn-link text-muted">Kembali ke Dashboard Utama</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

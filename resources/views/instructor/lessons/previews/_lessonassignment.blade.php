{{-- File ini hanya berisi konten pratinjau untuk pelajaran tugas --}}

<h4 class="font-weight-bold">{{ $lesson->title }}</h4>
<p>Ini adalah pratinjau halaman instruksi tugas yang akan dilihat oleh siswa.</p>
<hr>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Instruksi Tugas</h5>
        <div class="assignment-instructions">
            {!! nl2br(e($lesson->lessonable->instructions)) !!}
        </div>
        <hr>
        <p>
            <strong>Batas Waktu Pengumpulan:</strong> 
            {{ $lesson->lessonable->due_date ? $lesson->lessonable->due_date->format('d F Y, H:i') : 'Tidak ada' }}
        </p>
        <div class="text-center mt-4">
            <button class="btn btn-primary btn-lg" disabled>Kumpulkan Tugas</button>
        </div>
    </div>
</div>

<style>
    .assignment-instructions {
        line-height: 1.6;
        font-size: 15px;
        white-space: pre-wrap; /* Mempertahankan spasi dan baris baru */
    }
</style>
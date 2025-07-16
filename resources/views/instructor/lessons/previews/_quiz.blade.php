{{-- File ini hanya berisi konten pratinjau untuk pelajaran kuis --}}

<h4 class="font-weight-bold">{{ $lesson->title }}</h4>
<p>Ini adalah pratinjau halaman informasi yang akan dilihat siswa sebelum memulai kuis.</p>
<hr>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ $lesson->lessonable->title }}</h5>
        <p class="card-text">
            {!! nl2br(e($lesson->lessonable->description)) !!}
        </p>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <strong>Jumlah Soal:</strong> {{ $lesson->lessonable->questions->count() }} soal
            </li>
            <li class="list-group-item">
                <strong>Nilai Kelulusan:</strong> {{ $lesson->lessonable->pass_mark }}%
            </li>
            <li class="list-group-item">
                <strong>Batas Waktu:</strong> {{ $lesson->lessonable->time_limit ? $lesson->lessonable->time_limit . ' menit' : 'Tidak ada' }}
            </li>
        </ul>
        <div class="text-center mt-4">
            <button class="btn btn-primary btn-lg" disabled>Mulai Kuis</button>
        </div>
    </div>
</div>
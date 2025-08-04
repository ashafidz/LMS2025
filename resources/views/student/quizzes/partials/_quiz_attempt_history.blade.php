{{-- File ini menampilkan riwayat pengerjaan kuis dalam bentuk tabel --}}

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Riwayat Pengerjaan Anda</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tanggal & Waktu</th>
                        <th>Skor</th>
                        <th>Status</th>
                        <th>Durasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($allAttempts as $attempt)
                        <tr>
                            {{-- Nomor percobaan --}}
                            <td>{{ $loop->iteration }}</td>

                            {{-- Tanggal dan Waktu Mulai --}}
                            <td>{{ $attempt->start_time->isoFormat('D MMM YYYY, HH:mm') }}</td>

                            {{-- Skor --}}
                            <td>{{ rtrim(rtrim($attempt->score, '0'), '.') }}</td>

                            {{-- Status Lulus/Gagal --}}
                            <td>
                                @if ($attempt->status == 'passed')
                                    <span class="badge badge-success">Lulus</span>
                                @elseif($attempt->status == 'failed')
                                    <span class="badge badge-danger">Gagal</span>
                                @else
                                    <span class="badge badge-warning">Sedang Dikerjakan</span>
                                @endif
                            </td>

                            {{-- Durasi Pengerjaan --}}
                            <td>
                                @if ($attempt->end_time)
                                    {{-- Menggunakan diffForHumans untuk format yang mudah dibaca --}}
                                    {{ $attempt->start_time->diffForHumans($attempt->end_time, true) }}
                                @else
                                    -
                                @endif
                            </td>

                            {{-- Tombol Lihat Hasil --}}
                            <td>
                                @if ($attempt->status != 'in_progress')
                                    <a href="{{ route('student.quiz.result', $attempt->id) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fa fa-eye"></i> Lihat
                                    </a>
                                @else
                                    <a href="{{ route('student.quiz.take', $attempt->id) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fa fa-pencil"></i> Lanjutkan
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Anda belum pernah mengerjakan kuis ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

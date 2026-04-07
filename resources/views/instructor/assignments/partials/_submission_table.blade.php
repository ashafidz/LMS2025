{{-- resources/views/instructor/assignments/partials/_submission_table.blade.php --}}

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th class="text-center">NIM/NIDN/NIP</th>
                <th>Nama Siswa</th>
                <th>Waktu Pengumpulan</th>
                @if($showGrade ?? true)
                <th>Nilai</th>
                @endif
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($submissions as $submission)
                <tr>
                    <td class="text-center">{{ $submission->user->studentProfile->unique_id_number ? $submission->user->studentProfile->unique_id_number : '-' }}</td>
                    <td><a href="{{ route('profile.show', $submission->user->id) }}">{{ $submission->user->name }}</a></td>
                    {{-- <td>{{ $submission->submitted_at->format('d F Y, H:i') }}</td> --}}
                    <td>
                        {{ $submission->submitted_at->format('d F Y, H:i') }}
                        
                        {{-- Tambahkan badge jika terlambat --}}
                        @if ($submission->is_late)
                            <span class="badge badge-danger">Terlambat</span>
                        @endif
                    </td>
                    @if($showGrade ?? true)
                    <td>
                        @if(!is_null($submission->grade))
                            <span class="badge badge-inverse">{{ $submission->grade }} / 100</span>
                        @else
                            -
                        @endif
                    </td>
                    @endif
                    <td class="text-center">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#submissionModal-{{ $submission->id }}">
                            Lihat & Nilai
                        </button>

                        @if(!($showGrade ?? true))
                        <form action="{{ route('instructor.submission.grade', $submission) }}" method="POST" class="d-inline" onsubmit="return confirm('Anda yakin ingin langsung meminta siswa ini untuk revisi? (Tugas akan diberi nilai 0)');">
                            @csrf
                            <input type="hidden" name="grade" value="0">
                            <input type="hidden" name="feedback" value="Tugas belum sesuai kriteria. Silakan perbaiki dan kumpulkan kembali.">
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="fa fa-undo"></i> Revisi Cepat
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ ($showGrade ?? true) ? 5 : 4 }}" class="text-center">Tidak ada data pengumpulan di kategori ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
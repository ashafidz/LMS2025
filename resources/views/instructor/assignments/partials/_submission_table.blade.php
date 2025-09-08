{{-- resources/views/instructor/assignments/partials/_submission_table.blade.php --}}

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th class="text-center">NIM/NIDN/NIP</th>
                <th>Nama Siswa</th>
                <th>Waktu Pengumpulan</th>
                <th>Nilai</th>
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
                    <td>
                        @if(!is_null($submission->grade))
                            <span class="badge badge-inverse">{{ $submission->grade }} / 100</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#submissionModal-{{ $submission->id }}">
                            Lihat & Nilai
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada data pengumpulan di kategori ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
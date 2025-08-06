{{-- resources/views/instructor/assignments/partials/_submission_table.blade.php --}}

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>Waktu Pengumpulan</th>
                <th>Nilai</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($submissions as $submission)
                <tr>
                    <td>{{ $submission->user->name }}</td>
                    <td>{{ $submission->submitted_at->format('d F Y, H:i') }}</td>
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
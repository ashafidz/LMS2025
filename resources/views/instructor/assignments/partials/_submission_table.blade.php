{{-- resources/views/instructor/assignments/partials/_submission_table.blade.php --}}

@if(($allowBulkRevision ?? false) && $submissions->count() > 0)
<div class="mb-3 d-flex align-items-center">
    <button type="button" 
            class="btn btn-warning btn-sm btn-bulk-revise" 
            id="btn-bulk-revise-{{ $tabId ?? 'submitted' }}" 
            data-toggle="modal" 
            data-target="#bulkReviseModal-{{ $tabId ?? 'submitted' }}" 
            disabled>
        <i class="fa fa-undo"></i> Minta Revisi Terpilih (<span class="selected-count-{{ $tabId ?? 'submitted' }}">0</span>)
    </button>
    <span class="text-muted ml-3 small" id="selection-info-{{ $tabId ?? 'submitted' }}" style="display: none;">
        <span class="selected-count-{{ $tabId ?? 'submitted' }}">0</span> tugas dipilih
    </span>
</div>
@endif

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                @if($allowBulkRevision ?? false)
                <th style="width: 40px;" class="text-center">
                    <input type="checkbox" id="select-all-{{ $tabId ?? 'submitted' }}" class="select-all-checkbox" data-tab="{{ $tabId ?? 'submitted' }}" title="Pilih Semua">
                </th>
                @endif
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
                    @if($allowBulkRevision ?? false)
                    <td class="text-center">
                        <input type="checkbox" class="submission-checkbox submission-checkbox-{{ $tabId ?? 'submitted' }}" data-tab="{{ $tabId ?? 'submitted' }}" value="{{ $submission->id }}">
                    </td>
                    @endif
                    <td class="text-center">{{ $submission->user->studentProfile->unique_id_number ? $submission->user->studentProfile->unique_id_number : '-' }}</td>
                    <td><a href="{{ route('profile.show', $submission->user->id) }}">{{ $submission->user->name }}</a></td>
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
                @php $totalCols = 3 + (($showGrade ?? true) ? 1 : 0) + (($allowBulkRevision ?? false) ? 1 : 0) + 1; @endphp
                <tr>
                    <td colspan="{{ $totalCols }}" class="text-center">Tidak ada data pengumpulan di kategori ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
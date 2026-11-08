{{-- resources/views/instructor/assignments/partials/_submission_table.blade.php --}}

@if(($allowBulkRevision ?? false) && $submissions->count() > 0)
<div class="mb-3 d-flex align-items-center">
    <button type="button" 
            class="btn btn-secondary btn-sm btn-bulk-revise" 
            id="btn-bulk-revise-{{ $tabId ?? 'submitted' }}" 
            data-toggle="modal" 
            data-target="#bulkReviseModal-{{ $tabId ?? 'submitted' }}" 
            disabled>
        <i class="fa fa-undo"></i> <span>Minta Revisi Terpilih (<span class="selected-count-{{ $tabId ?? 'submitted' }}">0</span>)</span>
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
                    <input type="checkbox" id="select-all-{{ $tabId ?? 'submitted' }}" class="select-all-checkbox" data-tab="{{ $tabId ?? 'submitted' }}" title="Pilih semua di halaman ini" aria-label="Pilih semua di halaman ini">
                </th>
                @endif
                <th class="text-center">No</th>
                @include('instructor.partials._sort_th', ['key' => 'nim', 'tab' => $tabId ?? 'submitted', 'label' => 'NIM/NIDN/NIP', 'class' => 'text-center'])
                @include('instructor.partials._sort_th', ['key' => 'nama', 'tab' => $tabId ?? 'submitted', 'label' => 'Nama Siswa'])
                @include('instructor.partials._sort_th', ['key' => 'waktu', 'tab' => $tabId ?? 'submitted', 'label' => 'Waktu Pengumpulan'])
                @if($showGrade ?? true)
                @include('instructor.partials._sort_th', ['key' => 'nilai', 'tab' => $tabId ?? 'submitted', 'label' => 'Nilai', 'first' => 'desc'])
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
                    <td class="text-center">{{ $submissions->firstItem() + $loop->index }}</td>
                    <td class="text-center">{{ $submission->user->studentProfile?->unique_id_number ? $submission->user->studentProfile?->unique_id_number : '-' }}</td>
                    <td><a href="{{ route('profile.show', $submission->user->id) }}">{{ $submission->user->name }}</a></td>
                    <td>
                        {{ $submission->submitted_at->copy()->setTimezone(auth()->user()->timezone ?: session('user_timezone', config('app.timezone')))->locale('id')->isoFormat('D MMM YYYY, HH.mm') }}
                        
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
                    </td>
                </tr>
            @empty
                @php $totalCols = 4 + (($showGrade ?? true) ? 1 : 0) + (($allowBulkRevision ?? false) ? 1 : 0) + 1; @endphp
                <tr>
                    <td colspan="{{ $totalCols }}" class="text-center">{{ request()->filled('search') ? 'Tidak ada siswa yang cocok dengan pencarian di kategori ini.' : 'Tidak ada data pengumpulan di kategori ini.' }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-dash.pagination :items="$submissions" />

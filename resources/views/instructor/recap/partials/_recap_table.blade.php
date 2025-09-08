<div class="card shadow-sm mb-4">
    <div class="card-header custom-card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark">Rekap Nilai: {{ $module->title }}</h6>
        <div>
            <a href="{{ route('instructor.recap.download_pdf', $module->id) }}" class="btn btn-sm btn-danger me-2">
                <i class="fa fa-file-pdf-o"></i> PDF
            </a>
            <a href="{{ route('instructor.recap.download_excel', $module->id) }}" class="btn btn-sm btn-success">
                <i class="fa fa-file-excel-o"></i> Excel
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info small py-2 mb-3">
            <i class="fa fa-info-circle me-2"></i> Nilai kuis telah dikonversi ke skala 0-100 untuk kemudahan pembacaan. Rumus: (Skor yang diperoleh / Skor maksimum) × 100. Arahkan kursor ke nilai untuk melihat skor asli.
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">NIM/NIP/NIDN</th>
                        <th>Nama</th>
                        <th class="text-center">Poin Kursus</th>
                        <th class="text-center">Poin Modul</th>
                        @foreach($gradableLessons as $lesson)
                            <th class="text-center" title="{{ class_basename($lesson->lessonable_type) }}">
                                {{ $lesson->title }}
                                {{-- DIPERBARUI: Tampilkan nilai kelulusan absolut --}}
                                @if($lesson->lessonable_type === 'App\Models\Quiz')
                                    <br><small class="text-muted">(KKM: {{ $lesson->lessonable->minimum_passing_score }}%)</small>
                                @elseif($lesson->lessonable_type === 'App\Models\LessonAssignment')
                                    <br><small class="text-muted">(KKM: {{ $lesson->lessonable->pass_mark }})</small>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                    <tr>
                        <td class="text-center">{{ $student->studentProfile->unique_id_number ?? '-' }}</td>
                        <td><a href="{{ route('profile.show', $student->id) }}">{{ $student->name }}</a></td>
                        <td class="text-center">{{ $scores[$student->id]['total_course_points'] }}</td>
                        <td class="text-center">{{ $scores[$student->id]['total_module_points'] }}</td>
                        @foreach($gradableLessons as $lesson)
                            <td class="text-center">
                                @if($lesson->lessonable_type === 'App\Models\Quiz' && isset($scores[$student->id][$lesson->id.'_raw']))
                                    <span data-bs-toggle="tooltip" data-bs-placement="top" 
                                          title="Skor asli: {{ $scores[$student->id][$lesson->id.'_raw'] }} dari {{ $lesson->lessonable->max_possible_score }} poin">
                                        {{ $scores[$student->id][$lesson->id] }}
                                    </span>
                                @else
                                    {{ $scores[$student->id][$lesson->id] }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 4 + $gradableLessons->count() }}" class="text-center">Belum ada siswa yang terdaftar di kursus ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
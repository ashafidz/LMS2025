{{-- resources/views/instructor/leaderboards/_course_data_modal.blade.php --}}


        <h5>{{ $title }}</h5>


        <ul class="nav nav-tabs md-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#daftar_siswa" role="tab">Daftar Siswa</a>
                <div class="slide"></div>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#peringkat" role="tab">Peringkat</a>
                <div class="slide"></div>
            </li>
        </ul>
        <div class="tab-content card-block">
{{-- TAB 1: DAFTAR SEMUA SISWA (urut berdasarkan NIM) --}}
<div class="tab-pane active" id="daftar_siswa" role="tabpanel">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th class="text-center">NIM/NIP/NIDN</th>
                    <th>Nama Siswa</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($enrolledStudents as $student)
                    <tr>
                        <td class="text-center">{{ $student->studentProfile->unique_id_number ?? '-' }}</td>
                        <td><a href="{{ route('profile.show', $student->id) }}">{{ $student->name }}</a></td>
                        <td>{{ $student->email }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">Belum ada siswa yang terdaftar di kursus ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- TAB 2: PERINGKAT (urut berdasarkan poin) --}}
<div class="tab-pane" id="peringkat" role="tabpanel">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width: 10%;">Peringkat</th>
                    <th class="text-center">NIM/NIP/NIDN</th>
                    <th>Nama Siswa</th>
                    <th class="text-right">Total Poin</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($enrolledStudentsByPoints as $index => $student)
                    <tr class="
                        @if($index + 1 <= 5) table-success
                        @elseif($index + 1 <= 20) table-info
                        @else table-danger
                        @endif
                    ">
                        <td class="font-weight-bold">#{{ $index + 1 }}</td>
                        <td class="text-center">{{ $student->studentProfile->unique_id_number ?? '-' }}</td>
                        <td><a href="{{ route('profile.show', $student->id) }}">{{ $student->name }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($student->points_earned, 0, ',', '.') }} <span><i class="bi bi-star-fill text-warning"></i></span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada poin yang tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
        </div>


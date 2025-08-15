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
            {{-- TAB 1: DAFTAR SEMUA SISWA --}}
            <div class="tab-pane active" id="daftar_siswa" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">NIM/NIP/NIDN</th>
                                <th>Nama Siswa</th>
                                <th>Email</th>
                                <!--<th class="text-right">Poin Diperoleh</th>-->
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($enrolledStudents as $student)
                                <tr>
                                    <td class="text-center" >{{ $student->studentProfile->unique_id_number ? $student->studentProfile->unique_id_number : '-' }}</td>
                                    <td>{{ $student->name }}</td>
                                    <td>{{ $student->email }}</td>
                                    <!--<td class="text-right font-weight-bold">{{ number_format($student->points_earned, 0, ',', '.') }}</td>-->
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

            {{-- TAB 2: PERINGKAT --}}
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
                            @forelse ($leaderboardRanks as $index => $rank)
                    {{--
                        Dynamically set the row's background color based on rank ($index + 1):
                        - Ranks 1-3: Green (table-success) 🥇
                        - Ranks 4-20: Blue (table-info) 🔹
                        - Ranks >20: Gray (table-light) ⚪
                    --}}
                                <tr class="
                        @if($index + 1 <= 5)
                            table-success
                        @elseif($index + 1 <= 20)
                            table-info
                        @else
                            table-danger
                        @endif
                    " >
                                    <td class="font-weight-bold">#{{ $index + 1 }}</td>
                                    <td class="text-center" >{{ $rank->user->studentProfile->unique_id_number ? $rank->user->studentProfile->unique_id_number : '-' }}</td>
                                    <td>{{ $rank->user->name }}</td>
                                    <td class="text-right font-weight-bold">{{ number_format($rank->points_earned, 0, ',', '.') }} <span><i class="bi bi-star-fill text-warning"></ic></span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">Belum ada poin yang tercatat untuk dibuat peringkat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


{{-- resources/views/student/courses/partials/_leaderboard.blade.php --}}

<h4 class="font-weight-bold">Papan Peringkat (Leaderboard)</h4>
<p class="text-muted">Lihat peringkat Anda berdasarkan total poin yang diperoleh di kursus ini.</p>
<hr>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th style="width: 10%;">Peringkat</th>
                <th>Nama Siswa</th>
                <th class="text-right">Total Poin</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($leaderboardRanks as $index => $rank)
                @if ($rank->user)
                    {{--
                        Dynamically set the row's background color based on rank ($index + 1):
                        - Ranks 1-3: Green (table-success)
                        - Ranks 4-20: Blue (table-info)
                        - Ranks >20: Gray (table-light)
                    --}}
                    <tr class="
                        @if($index + 1 <= 3)
                            table-success
                        @elseif($index + 1 <= 20)
                            table-info
                        @else
                            table-light
                        @endif
                    ">
                        <td class="font-weight-bold">#{{ $index + 1 }}</td>
                        <td>
                            {{ $rank->user->name }}
                            {{-- Keep the badge to highlight the current user --}}
                            @if($rank->user_id === Auth::id())
                                <span class="badge badge-primary">Anda</span>
                            @endif
                        </td>
                        <td class="text-right font-weight-bold">{{ number_format($rank->points_earned, 0, ',', '.') }}</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted">Belum ada peringkat untuk kursus ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
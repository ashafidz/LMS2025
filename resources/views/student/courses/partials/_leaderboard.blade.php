{{-- resources/views/student/courses/partials/_leaderboard.blade.php --}}

<h4 class="font-weight-bold rs-lesson-heading">Papan Peringkat (Leaderboard)</h4>
<p class="text-muted">Lihat peringkat Anda berdasarkan total poin yang diperoleh di kursus ini.</p>
<hr>

<div class="table-responsive">
    <table class="table rs-lb">
        <thead>
            <tr>
                <th style="width: 10%;">Peringkat</th>
                <th class="text-center" >NIM/NIDN/NIP</th>
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
                    @php $rankTier = $index + 1 <= 5 ? 'top' : ($index + 1 <= 20 ? 'mid' : 'rest'); @endphp
                    <tr class="rs-lb-row rs-lb-row--{{ $rankTier }} {{ $rank->user_id == Auth::id() ? 'is-me' : '' }}">
                        <td><span class="rs-rank rs-rank--{{ $rankTier }}">#{{ $index + 1 }}</span></td>
                        <td class="text-center" >{{ $rank->user->studentProfile?->unique_id_number ? $rank->user->studentProfile?->unique_id_number : '-' }}</td>
                        <td>
                            <a href="{{ route('profile.show', $rank->user->id) }}">
                                {{ $rank->user->name }}
                                {{-- Keep the badge to highlight the current user --}}
                                @if($rank->user_id == Auth::id())
                                    <span class="badge badge-primary">Anda</span>
                                @endif
                            </a>
                        </td>
                        <td class="text-right font-weight-bold">{{ number_format($rank->points_earned ?? $rank->total_points, 0, ',', '.') }} <span><i class="bi bi-star-fill text-warning"></i></span></td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">Belum ada peringkat untuk kursus ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
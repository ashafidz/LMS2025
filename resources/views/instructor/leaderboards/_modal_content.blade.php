{{-- resources/views/instructor/leaderboards/_modal_content.blade.php --}}

<h4 class="font-weight-bold">{{ $title }}</h4>
<p class="text-muted">Menampilkan peringkat teratas berdasarkan total poin yang diperoleh.</p>
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
                @if ($rank->user) {{-- Safety check to prevent errors if the user was deleted --}}
                    {{--
                        Dynamically set the row's background color based on rank ($index + 1):
                        - Ranks 1-3: Green (table-success) 🥇
                        - Ranks 4-20: Blue (table-info) 🔹
                        - Ranks >20: Gray (table-light) ⚪
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
                        <td>{{ $rank->user->name }}</td>
                        {{-- Use 'points_earned' for course leaderboards, 'total_points' for modules --}}
                        <td class="text-right font-weight-bold">{{ number_format($rank->points_earned ?? $rank->total_points, 0, ',', '.') }}</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted">Belum ada poin yang tercatat.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
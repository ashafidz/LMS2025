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
                <tr>
                    <td class="font-weight-bold">#{{ $index + 1 }}</td>
                    <td>{{ $rank->user->name }}</td>
                    {{-- Gunakan 'points_earned' untuk leaderboard kursus, 'total_points' untuk modul --}}
                    <td class="text-right font-weight-bold">{{ number_format($rank->points_earned ?? $rank->total_points, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted">Belum ada poin yang tercatat.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
{{-- resources/views/student/courses/partials/_leaderboard.blade.php --}}
<h4 class="font-weight-bold">Papan Peringkat: {{ $module->title }}</h4>
<p class="text-muted">Lihat peringkat Anda berdasarkan total poin yang diperoleh di modul ini.</p>
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
                <tr class="{{ $rank->user_id === Auth::id() ? 'table-info' : '' }}">
                    <td class="font-weight-bold">#{{ $index + 1 }}</td>
                    <td>
                        {{ $rank->user->name }}
                        @if($rank->user_id === Auth::id())
                            <span class="badge badge-primary">Anda</span>
                        @endif
                    </td>
                    <td class="text-right font-weight-bold">{{ number_format($rank->total_points, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted">Belum ada poin yang tercatat di modul ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@php
    $query = $polling->options()->withCount('responses');
    if ($polling->show_voters) {
        $query->with(['responses' => function($q) {
            $q->with('user.studentProfile');
        }]);
    }
    $options = $query->get();
    
    // Count distinct users who voted, not total responses
    $totalVoters = $polling->responses()->distinct('user_id')->count('user_id');
    $totalResponses = $polling->responses()->count();
    
    $chartLabels = $options->pluck('text')->toArray();
    $chartData = $options->pluck('responses_count')->toArray();
@endphp

<div class="row">
    <div class="col-md-6">
        <ul class="list-group mb-4">
            @foreach($options as $option)
            @php
                $percentage = $totalVoters > 0 ? round(($option->responses_count / $totalVoters) * 100) : 0;
            @endphp
            <li class="list-group-item">
                <div class="d-flex justify-content-between mb-1">
                    <span>{{ $option->text }}</span>
                    <strong>{{ $percentage }}% ({{ $option->responses_count }} suara)</strong>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                @if($polling->show_voters && $option->responses_count > 0)
                    <div class="mt-2 d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Ada {{ $option->responses_count }} pemilih</span>
                        <button class="btn btn-xs btn-outline-info" onclick="$('body').append($('#modalAjaxOption{{ $option->id }}')); $('#modalAjaxOption{{ $option->id }}').modal('show');">Lihat Pemilih</button>
                    </div>
                @endif
            </li>
            @endforeach
        </ul>
        <p class="text-muted small">Total Suara: {{ $totalResponses }}</p>
    </div>
    <div class="col-md-6">
        <div style="position: relative; height:250px;">
            <canvas id="pollingChartAjax-{{ $polling->id }}"></canvas>
        </div>
    </div>
</div>


<!-- Modals dipindahkan ke luar list, dan script di atas akan append ke body saat di-click agar tidak terperangkap -->
@if($polling->show_voters)
    @foreach($options as $option)
        @if($option->responses_count > 0)
        <div class="modal fade" id="modalAjaxOption{{ $option->id }}" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1050;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pemilih: {{ $option->text }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0" style="max-height: 400px; overflow-y: auto;">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="50">No</th>
                                        <th>NRP / NIM</th>
                                        <th>Nama Lengkap</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($option->responses as $idx => $response)
                                    <tr>
                                        <td class="text-center">{{ $idx + 1 }}</td>
                                        <td>{{ $response->user->studentProfile->unique_id_number ?? '-' }}</td>
                                        <td>
                                            {{ $response->user->name ?? 'Anonim' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endforeach
@endif

<script>
    // Ensure Chart.js is loaded (if not, we could dynamically load it)
    if (typeof Chart === 'undefined') {
        let script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        script.onload = function() {
            renderChart{{ $polling->id }}();
        };
        document.head.appendChild(script);
    } else {
        renderChart{{ $polling->id }}();
    }

    function renderChart{{ $polling->id }}() {
        const ctx = document.getElementById('pollingChartAjax-{{ $polling->id }}').getContext('2d');
        const labels = {!! json_encode($chartLabels) !!};
        const data = {!! json_encode($chartData) !!};

        const backgroundColors = [
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 99, 132, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)'
        ];

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Suara',
                    data: data,
                    backgroundColor: backgroundColors.slice(0, data.length),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
</script>

@php
    $query = $polling->options()->withCount('responses');
    if ($polling->show_voters) {
        $query->with(['responses' => function($q) {
            $q->with('user');
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
                    <div class="mt-2 small text-muted">
                        <strong>Pemilih:</strong>
                        {{ implode(', ', $option->responses->map(function($r) { return $r->user->name ?? 'User'; })->toArray()) }}
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

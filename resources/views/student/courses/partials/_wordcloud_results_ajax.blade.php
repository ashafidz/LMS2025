<div class="wordcloud-results-container mt-4">
    <div class="card bg-light">
        <div class="card-header bg-white">
            <h6 class="mb-0">Hasil Word Cloud Live</h6>
        </div>
        <div class="card-body">
            @if(count($wordCloudList) > 0)
                <div class="text-center" style="position: relative; min-height: 300px;">
                    <!-- Canvas for WordCloud -->
                    <canvas id="studentWordCloudCanvas" width="600" height="300" style="width: 100%; height: auto;"></canvas>
                </div>
            @else
                <div class="alert alert-info text-center">Belum ada kata yang dikirimkan. Jadilah yang pertama!</div>
            @endif
        </div>
    </div>
</div>

@if(count($wordCloudList) > 0)
<script>
    // Pastikan WordCloud2 library dimuat
    if (typeof WordCloud === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/wordcloud2.js/1.2.2/wordcloud2.min.js';
        script.onload = function() {
            renderStudentWordCloud();
        };
        document.head.appendChild(script);
    } else {
        renderStudentWordCloud();
    }

    function renderStudentWordCloud() {
        const wordList = {!! json_encode($wordCloudList) !!};
        const canvas = document.getElementById('studentWordCloudCanvas');
        
        if (canvas) {
            const colors = ['#007bff', '#28a745', '#17a2b8', '#ffc107', '#fd7e14', '#e83e8c', '#6f42c1'];
            
            WordCloud(canvas, {
                list: wordList,
                gridSize: Math.round(16 * canvas.offsetWidth / 1024),
                weightFactor: function (size) {
                    return Math.pow(size, 0.8) * 20; 
                },
                fontFamily: 'Inter, Roboto, sans-serif',
                color: function (word, weight) {
                    return colors[Math.floor(Math.random() * colors.length)];
                },
                rotateRatio: 0.2,
                rotationSteps: 2,
                backgroundColor: 'transparent',
                shape: 'circle',
            });
        }
    }
</script>
@endif

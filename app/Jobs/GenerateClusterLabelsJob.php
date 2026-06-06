<?php

namespace App\Jobs;

use App\Models\KmeansRun;
use App\Services\N8nService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateClusterLabelsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30; // detik

    public function __construct(private KmeansRun $run) {}

    public function handle(N8nService $n8n): void
    {
        $this->run->update([
            'ai_labeling_status' => 'processing',
            'ai_labeling_requested_at' => now()
        ]);

        $clusterData = $this->buildClusterPayload();

        $success = $n8n->triggerClusterLabeling($this->run, $clusterData);

        if (!$success) {
            $this->run->update(['ai_labeling_status' => 'failed']);
            throw new \Exception('n8n webhook failed');
        }
    }

    private function buildClusterPayload(): array
    {
        $clusters = [];
        $summary = $this->run->result_summary ?? [];
        $kValue = $this->run->k_value;

        for ($i = 1; $i <= $kValue; $i++) {
            $clusterKey = "Cluster {$i}";
            if (isset($summary[$clusterKey])) {
                $clusters[] = [
                    'cluster_number' => $i,
                    'centroid' => $summary[$clusterKey]['centroid'] ?? [],
                    'student_count' => $summary[$clusterKey]['size'] ?? 0
                ];
            }
        }

        return $clusters;
    }
}

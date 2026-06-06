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
        $kValue = $this->run->k_value;

        $assignments = \App\Models\KmeansClusterAssignment::with(['attempt.componentScores.dimension'])
            ->where('run_id', $this->run->id)
            ->get();

        for ($i = 1; $i <= $kValue; $i++) {
            $clusterAssignments = $assignments->where('cluster_number', $i);
            $studentCount = $clusterAssignments->count();

            if ($studentCount === 0) continue;

            $mastery = 0; $performance = 0; $knowledge = 0;
            $autonomy = 0; $competence = 0;

            foreach ($clusterAssignments as $assignment) {
                $scores = $assignment->attempt->componentScores;
                $mastery += $scores->where('dimension.name', 'Mastery Goal')->first()?->contribution_pct ?? 0;
                $performance += $scores->where('dimension.name', 'Performance Goal')->first()?->contribution_pct ?? 0;
                $knowledge += $scores->whereNull('dimension_id')->whereNull('component_id')->first()?->average_score ?? 0;
                $autonomy += $scores->where('dimension.name', 'Autonomy')->first()?->contribution_pct ?? 0;
                $competence += $scores->where('dimension.name', 'Competence')->first()?->contribution_pct ?? 0;
            }

            $clusters[] = [
                'cluster_number' => $i,
                'student_count' => $studentCount,
                'mastery' => round($mastery / $studentCount, 2),
                'performance' => round($performance / $studentCount, 2),
                'knowledge' => round($knowledge / $studentCount, 2),
                'autonomy' => round($autonomy / $studentCount, 2),
                'competence' => round($competence / $studentCount, 2)
            ];
        }

        return $clusters;
    }
}

<?php

namespace App\Services;

use App\Models\KmeansRun;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class N8nService
{
    /**
     * Trigger n8n workflow for AI cluster labeling.
     */
    public function triggerClusterLabeling(KmeansRun $run, array $clusterData): bool
    {
        $baseUrl = env('N8N_BASE_URL');
        $webhookPath = env('N8N_CLUSTER_LABELING_WEBHOOK');
        $secretToken = env('N8N_SECRET_TOKEN');

        if (!$baseUrl || !$webhookPath) {
            Log::error('N8N configuration is missing');
            return false;
        }

        $url = rtrim($baseUrl, '/') . '/' . ltrim($webhookPath, '/');

        try {
            $response = Http::withHeaders([
                'X-N8N-Token' => $secretToken,
                'Accept' => 'application/json',
            ])->post($url, [
                'run_id' => $run->id,
                'course_id' => $run->course_id,
                'course_name' => $run->course->title ?? 'Unknown Course',
                'clusters' => $clusterData
            ]);

            if ($response->successful()) {
                Log::info("Successfully triggered n8n webhook for KmeansRun ID: {$run->id}");
                return true;
            }

            Log::error("Failed to trigger n8n webhook. Status: {$response->status()}", [
                'body' => $response->body()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error("Exception triggering n8n webhook: " . $e->getMessage());
            return false;
        }
    }
}

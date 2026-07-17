<?php

namespace App\Jobs;

use App\Models\Course;
use App\Models\AiGenerationJob;
use App\Models\AdaptiveModule;
use App\Services\AdaptiveAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecommendArchetypesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    protected AiGenerationJob $jobRecord;
    protected AdaptiveModule $module;

    public function __construct(AiGenerationJob $jobRecord, AdaptiveModule $module)
    {
        $this->jobRecord = $jobRecord;
        $this->module = $module;
    }

    public function handle(AdaptiveAiService $aiService): void
    {
        $this->jobRecord->update([
            'status' => 'processing',
            'progress' => 10,
            'message' => 'Menganalisis konten modul...'
        ]);

        Log::info("Starting AI Recommend Archetypes Job", [
            'job_id' => $this->jobRecord->id,
            'module_id' => $this->module->id
        ]);

        $this->jobRecord->update([
            'progress' => 50,
            'message' => 'Meminta rekomendasi dari AI...'
        ]);

        $recommendations = $aiService->recommendArchetypes($this->module);

        if ($recommendations && isset($recommendations['recommended_archetypes'])) {
            $archetypes = $recommendations['recommended_archetypes'];
            
            // Save directly to database
            $this->module->update([
                'target_archetypes' => $archetypes
            ]);

            $this->jobRecord->update([
                'status' => 'completed',
                'progress' => 100,
                'message' => 'Berhasil memberikan ' . count($archetypes) . ' rekomendasi profil.'
            ]);
            
            Log::info("AI Recommend Archetypes Success", ['job_id' => $this->jobRecord->id]);
        } else {
            $this->jobRecord->update([
                'status' => 'failed',
                'message' => 'Gagal mendapatkan rekomendasi',
                'error' => 'Model AI tidak memberikan respon yang valid.'
            ]);
            
            Log::error("AI Recommend Archetypes Failed", ['job_id' => $this->jobRecord->id]);
        }
    }
}

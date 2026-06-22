<?php

namespace App\Jobs;

use App\Models\Course;
use App\Models\AdaptiveModule;
use App\Services\AdaptiveAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateAdaptiveContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes max for Ollama

    protected $course;
    protected $archetype;
    protected $moduleCount;
    protected $lessonCount;
    protected $extraTopics;

    public function __construct(Course $course, string $archetype, int $moduleCount, int $lessonCount, ?string $extraTopics = null)
    {
        $this->course = $course;
        $this->archetype = $archetype;
        $this->moduleCount = $moduleCount;
        $this->lessonCount = $lessonCount;
        $this->extraTopics = $extraTopics;
    }

    public function handle(AdaptiveAiService $aiService): void
    {
        $cacheKey = "adaptive_ai_job_{$this->job->getJobId()}";
        
        Cache::put($cacheKey, ['status' => 'processing', 'message' => 'Generating modules...'], 3600);

        Log::info("Starting AI Generation Job", [
            'course' => $this->course->id,
            'archetype' => $this->archetype,
            'job_id' => $this->job->getJobId()
        ]);

        $result = $aiService->generateFull(
            $this->course, 
            $this->archetype, 
            $this->moduleCount, 
            $this->lessonCount, 
            $this->extraTopics
        );

        if (!$result || !isset($result['curriculum'])) {
            Cache::put($cacheKey, ['status' => 'failed', 'message' => 'Gagal mendapatkan response valid dari AI.'], 3600);
            return;
        }

        Cache::put($cacheKey, ['status' => 'processing', 'message' => 'Menyimpan ke database...'], 3600);

        // Save to Database
        $lastOrderMod = AdaptiveModule::where('course_id', $this->course->id)
                                      ->where('archetype_name', $this->archetype)
                                      ->max('order') ?? -1;

        foreach ($result['curriculum'] as $modData) {
            $lastOrderMod++;
            
            $module = $this->course->adaptiveModules()->create([
                'archetype_name' => $this->archetype,
                'title'          => $modData['title'],
                'description'    => $modData['description'] ?? null,
                'order'          => $lastOrderMod,
                'ai_generated'   => true,
            ]);

            if (!empty($modData['lessons'])) {
                $lastOrderLes = -1;
                foreach ($modData['lessons'] as $lesData) {
                    $lastOrderLes++;
                    $module->lessons()->create([
                        'title'        => $lesData['title'],
                        'content'      => $lesData['content'] ?? '<p>Konten tidak tersedia.</p>',
                        'order'        => $lastOrderLes,
                        'ai_generated' => true,
                    ]);
                }
            }
        }

        Cache::put($cacheKey, ['status' => 'completed', 'message' => 'Berhasil membuat curriculum.'], 3600);
        Log::info("AI Generation Job Completed", ['job_id' => $this->job->getJobId()]);
    }
}

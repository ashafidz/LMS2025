<?php

namespace App\Jobs;

use App\Models\Course;
use App\Models\AiGenerationJob;
use App\Models\AiReference;
use App\Models\AdaptiveModule;
use App\Services\AdaptiveAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAdaptiveContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes max for Ollama

    protected AiGenerationJob $jobRecord;
    protected array $params;

    public function __construct(AiGenerationJob $jobRecord, array $params)
    {
        $this->jobRecord = $jobRecord;
        $this->params = $params;
    }

    public function handle(AdaptiveAiService $aiService): void
    {
        $this->jobRecord->update([
            'status' => 'processing',
            'progress' => 10,
            'message' => 'Menyiapkan referensi RAG...'
        ]);

        $course = $this->jobRecord->course;
        $archetype = $this->jobRecord->archetype_name;

        // Fetch References for this course and archetype
        $references = AiReference::where('course_id', $course->id)
            ->where(function($q) use ($archetype) {
                $q->where('archetype_name', $archetype)
                  ->orWhereNull('archetype_name');
            })->get();

        $ragContexts = [];
        foreach ($references as $ref) {
            // Limit to 10k chars per ref to avoid huge context windows, though llama3 supports 8k normally.
            $ragContexts[] = substr($ref->extracted_text, 0, 10000); 
        }

        $this->jobRecord->update([
            'progress' => 30,
            'message' => 'Menghubungi AI Model (memakan waktu beberapa menit)...'
        ]);

        $lessonCount = $this->params['lesson_count'] ?? 2;
        $extraTopics = $this->params['extra_topics'] ?? null;

        Log::info("Starting AI Generation Job", [
            'job_id' => $this->jobRecord->id,
            'type'   => $this->jobRecord->type,
            'course' => $course->id,
            'archetype' => $archetype
        ]);

        // ─── LESSONS-ONLY MODE ───────────────────────────────────
        if ($this->jobRecord->type === 'lessons') {
            $module = AdaptiveModule::findOrFail($this->params['module_id']);

            $result = $aiService->generateLessonsForModule(
                $course,
                $archetype,
                $module,
                $lessonCount,
                $extraTopics,
                $ragContexts
            );

            if (!$result || !isset($result['lessons'])) {
                $this->jobRecord->update([
                    'status' => 'failed', 'progress' => 0,
                    'message' => 'Gagal mendapatkan response.',
                    'error' => 'AI tidak merespons dengan format JSON yang valid atau terputus.'
                ]);
                return;
            }

            $this->jobRecord->update(['progress' => 80, 'message' => 'Menyimpan lesson ke database...']);

            $lastOrderLes = \App\Models\AdaptiveLesson::where('adaptive_module_id', $module->id)->max('order') ?? -1;
            foreach ($result['lessons'] as $lesData) {
                $lastOrderLes++;
                $module->lessons()->create([
                    'title'        => $lesData['title'],
                    'content'      => $lesData['content'] ?? '<p>Konten tidak tersedia.</p>',
                    'order'        => $lastOrderLes,
                    'ai_generated' => true,
                ]);
            }

            $this->jobRecord->update(['status' => 'completed', 'progress' => 100, 'message' => 'Selesai! Lesson berhasil ditambahkan.']);
            return;
        }

        // ─── MODULES / FULL CURRICULUM MODE ─────────────────────
        $moduleCount = $this->params['module_count'] ?? 1;

        $result = $aiService->generateCurriculum(
            $course, 
            $archetype, 
            $moduleCount, 
            $lessonCount, 
            $extraTopics,
            $ragContexts
        );

        if (!$result || !isset($result['curriculum'])) {
            $this->jobRecord->update([
                'status' => 'failed',
                'progress' => 0,
                'message' => 'Gagal mendapatkan response.',
                'error' => 'AI tidak merespons dengan format JSON yang valid atau terputus.'
            ]);
            return;
        }

        $this->jobRecord->update([
            'progress' => 80,
            'message' => 'Menyimpan hasil ke database...'
        ]);

        // Save to Database
        $lastOrderMod = AdaptiveModule::where('course_id', $course->id)
                                      ->where('archetype_name', $archetype)
                                      ->max('order') ?? -1;

        foreach ($result['curriculum'] as $modData) {
            $lastOrderMod++;
            
            $module = $course->adaptiveModules()->create([
                'archetype_name' => $archetype,
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

        $this->jobRecord->update([
            'status' => 'completed',
            'progress' => 100,
            'message' => 'Selesai! Kurikulum berhasil dibuat.'
        ]);
        
        Log::info("AI Generation Job Completed", ['job_id' => $this->jobRecord->id]);
    }
}

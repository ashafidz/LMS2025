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

    public $timeout = 3600; // 1 hour max for Ollama

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

        // Fetch References for this course
        $references = AiReference::where('course_id', $course->id)->get();

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
            'course' => $course->id
        ]);

        // ─── LESSONS-ONLY MODE ───────────────────────────────────
        if ($this->jobRecord->type === 'lessons') {
            $module = AdaptiveModule::findOrFail($this->params['module_id']);

            $result = $aiService->generateLessonsForModule(
                $course, $module, $lessonCount, $extraTopics, $ragContexts
            );

            if (!$result || !isset($result['lessons'])) {
                $this->jobRecord->update(['status' => 'failed', 'progress' => 0,
                    'message' => 'Gagal mendapatkan response.',
                    'error' => 'AI tidak merespons dengan format JSON yang valid atau terputus.']);
                return;
            }

            $this->jobRecord->update(['progress' => 80, 'message' => 'Menyimpan lesson ke database...']);

            $lastOrderLes = \App\Models\AdaptiveLesson::where('adaptive_module_id', $module->id)->max('order') ?? -1;
            foreach ($result['lessons'] as $lesData) {
                $lastOrderLes++;
                $module->lessons()->create([
                    'title'        => $lesData['title'],
                    'lesson_type'  => 'article',
                    'content'      => $lesData['content'] ?? '<p>Konten tidak tersedia.</p>',
                    'order'        => $lastOrderLes,
                    'ai_generated' => true,
                ]);
            }

            $this->jobRecord->update(['status' => 'completed', 'progress' => 100, 'message' => 'Selesai! Lesson berhasil ditambahkan.']);
            return;
        }

        // ─── ASSIGNMENTS MODE ─────────────────────────────────────
        if ($this->jobRecord->type === 'assignments') {
            $module = AdaptiveModule::findOrFail($this->params['module_id']);

            $result = $aiService->generateAssignmentLessons(
                $course, $module, $lessonCount, $extraTopics, $ragContexts
            );

            if (!$result || !isset($result['assignments'])) {
                $this->jobRecord->update(['status' => 'failed', 'progress' => 0,
                    'message' => 'Gagal mendapatkan response.',
                    'error' => 'AI tidak merespons dengan format JSON yang valid atau terputus.']);
                return;
            }

            $this->jobRecord->update(['progress' => 80, 'message' => 'Menyimpan penugasan ke database...']);

            $lastOrderLes = \App\Models\AdaptiveLesson::where('adaptive_module_id', $module->id)->max('order') ?? -1;
            foreach ($result['assignments'] as $asgData) {
                $lastOrderLes++;
                $module->lessons()->create([
                    'title'                   => $asgData['title'],
                    'lesson_type'             => 'assignment',
                    'content'                 => $asgData['description'] ?? '<p>Deskripsi penugasan tidak tersedia.</p>',
                    'assignment_instructions' => $asgData['instructions'] ?? null,
                    'assignment_max_score'    => $asgData['max_score'] ?? 100,
                    'order'                   => $lastOrderLes,
                    'ai_generated'            => true,
                ]);
            }

            $this->jobRecord->update(['status' => 'completed', 'progress' => 100, 'message' => 'Selesai! Penugasan berhasil dibuat.']);
            return;
        }

        // ─── QUIZZES MODE ─────────────────────────────────────────
        if ($this->jobRecord->type === 'quizzes') {
            $module = AdaptiveModule::findOrFail($this->params['module_id']);

            $result = $aiService->generateQuizLessons(
                $course, $module, $lessonCount, $extraTopics, $ragContexts
            );

            if (!$result || !isset($result['quizzes'])) {
                $this->jobRecord->update(['status' => 'failed', 'progress' => 0,
                    'message' => 'Gagal mendapatkan response.',
                    'error' => 'AI tidak merespons dengan format JSON yang valid atau terputus.']);
                return;
            }

            $this->jobRecord->update(['progress' => 80, 'message' => 'Menyimpan quiz ke database...']);

            $lastOrderLes = \App\Models\AdaptiveLesson::where('adaptive_module_id', $module->id)->max('order') ?? -1;
            foreach ($result['quizzes'] as $quizData) {
                $lastOrderLes++;
                $module->lessons()->create([
                    'title'        => $quizData['title'],
                    'lesson_type'  => 'quiz',
                    'content'      => $quizData['description'] ?? '<p>Quiz.</p>',
                    'quiz_data'    => $quizData['questions'] ?? [],
                    'order'        => $lastOrderLes,
                    'ai_generated' => true,
                ]);
            }

            $this->jobRecord->update(['status' => 'completed', 'progress' => 100, 'message' => 'Selesai! Quiz berhasil dibuat.']);
            return;
        }

        // ─── MODULES / FULL CURRICULUM MODE ─────────────────────
        $moduleCount = $this->params['module_count'] ?? 1;

        $this->jobRecord->update(['progress' => 15, 'message' => 'Menghasilkan struktur modul...']);

        // 1. Generate Modules only
        $result = $aiService->generateModules(
            $course, 
            $moduleCount, 
            $extraTopics,
            $ragContexts
        );

        if (!$result || !isset($result['modules'])) {
            $this->jobRecord->update([
                'status' => 'failed',
                'progress' => 0,
                'message' => 'Gagal mendapatkan response.',
                'error' => 'AI tidak merespons dengan format JSON struktur modul yang valid atau terputus.'
            ]);
            return;
        }

        $this->jobRecord->update([
            'progress' => 30,
            'message' => 'Menyimpan struktur modul ke database...'
        ]);

        // Save Modules to Database
        $lastOrderMod = AdaptiveModule::where('course_id', $course->id)
                                      ->max('order') ?? -1;

        $createdModules = [];
        foreach ($result['modules'] as $modData) {
            $lastOrderMod++;
            $module = $course->adaptiveModules()->create([
                'title'             => $modData['title'],
                'description'       => $modData['description'] ?? null,
                'target_archetypes' => [], // Unassigned by default
                'order'             => $lastOrderMod,
                'ai_generated'      => true,
            ]);
            $createdModules[] = $module;
        }

        // 2. If FULL mode, generate contents per module sequentially
        if ($this->jobRecord->type === 'full') {
            $totalMods = count($createdModules);
            $progressPerMod = 60 / ($totalMods > 0 ? $totalMods : 1);
            $currentProgress = 30;

            foreach ($createdModules as $index => $module) {
                $this->jobRecord->update([
                    'progress' => min(90, round($currentProgress)),
                    'message' => 'Menghasilkan konten untuk Modul ' . ($index + 1) . ' dari ' . $totalMods . '...'
                ]);

                if ($this->jobRecord->fresh()->status === 'failed') return;
                
                // Generate Articles
                $articleRes = $aiService->generateLessonsForModule($course, $module, $lessonCount, $extraTopics, $ragContexts);
                $lastOrderLes = \App\Models\AdaptiveLesson::where('adaptive_module_id', $module->id)->max('order') ?? -1;
                
                if ($articleRes && isset($articleRes['lessons'])) {
                    foreach ($articleRes['lessons'] as $lesData) {
                        $lastOrderLes++;
                        $module->lessons()->create([
                            'title'        => $lesData['title'],
                            'lesson_type'  => 'article',
                            'content'      => $lesData['content'] ?? '<p>Konten tidak tersedia.</p>',
                            'order'        => $lastOrderLes,
                            'ai_generated' => true,
                        ]);
                    }
                }

                // Generate Quiz (1 Quiz per module by default)
                if ($this->jobRecord->fresh()->status === 'failed') return;
                $this->jobRecord->update(['message' => 'Menghasilkan Quiz untuk Modul ' . ($index + 1) . '...']);
                $quizRes = $aiService->generateQuizLessons($course, $module, 1, $extraTopics, $ragContexts);
                if ($quizRes && isset($quizRes['quizzes'])) {
                    foreach ($quizRes['quizzes'] as $quizData) {
                        $lastOrderLes++;
                        $module->lessons()->create([
                            'title'        => $quizData['title'],
                            'lesson_type'  => 'quiz',
                            'content'      => $quizData['description'] ?? '<p>Quiz.</p>',
                            'quiz_data'    => $quizData['questions'] ?? [],
                            'order'        => $lastOrderLes,
                            'ai_generated' => true,
                        ]);
                    }
                }

                // Generate Assignment (1 Assignment per module by default)
                if ($this->jobRecord->fresh()->status === 'failed') return;
                $this->jobRecord->update(['message' => 'Menghasilkan Penugasan untuk Modul ' . ($index + 1) . '...']);
                $asgRes = $aiService->generateAssignmentLessons($course, $module, 1, $extraTopics, $ragContexts);
                if ($asgRes && isset($asgRes['assignments'])) {
                    foreach ($asgRes['assignments'] as $asgData) {
                        $lastOrderLes++;
                        $module->lessons()->create([
                            'title'                   => $asgData['title'],
                            'lesson_type'             => 'assignment',
                            'content'                 => $asgData['description'] ?? '<p>Deskripsi penugasan tidak tersedia.</p>',
                            'assignment_instructions' => $asgData['instructions'] ?? null,
                            'assignment_max_score'    => $asgData['max_score'] ?? 100,
                            'order'                   => $lastOrderLes,
                            'ai_generated'            => true,
                        ]);
                    }
                }

                $currentProgress += $progressPerMod;
            }
        }

        $this->jobRecord->update([
            'status' => 'completed',
            'progress' => 100,
            'message' => 'Selesai! Kurikulum berhasil dibuat.'
        ]);
        
        Log::info("AI Generation Job Completed", ['job_id' => $this->jobRecord->id]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateAdaptiveContentJob Failed', [
            'job_id' => $this->jobRecord->id ?? null,
            'error'  => $exception->getMessage(),
            'trace'  => $exception->getTraceAsString()
        ]);

        if (isset($this->jobRecord)) {
            $this->jobRecord->update([
                'status'  => 'failed',
                'message' => 'Job gagal karena kesalahan sistem atau timeout.',
                'error'   => $exception->getMessage()
            ]);
        }
    }
}

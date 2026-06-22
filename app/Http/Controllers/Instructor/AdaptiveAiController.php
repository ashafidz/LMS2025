<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\AdaptiveModule;
use App\Services\AdaptiveAiService;
use App\Jobs\GenerateAdaptiveContentJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdaptiveAiController extends Controller
{
    protected AdaptiveAiService $aiService;

    public function __construct(AdaptiveAiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Mode A: Generate Modul Saja (Synchronous)
     */
    public function generateModules(Course $course, Request $request)
    {
        $validated = $request->validate([
            'archetype_name' => 'required|string',
            'count'          => 'required|integer|min:1|max:10',
            'extra_topics'   => 'nullable|string'
        ]);

        $result = $this->aiService->generateModules($course, $validated['archetype_name'], $validated['count'], $validated['extra_topics']);

        if (!$result || !isset($result['modules'])) {
            return back()->with('error', 'Gagal menghasilkan modul dari AI. Pastikan server Ollama berjalan.');
        }

        $lastOrder = AdaptiveModule::where('course_id', $course->id)
                                   ->where('archetype_name', $validated['archetype_name'])
                                   ->max('order') ?? -1;

        foreach ($result['modules'] as $mod) {
            $lastOrder++;
            $course->adaptiveModules()->create([
                'archetype_name' => $validated['archetype_name'],
                'title'          => $mod['title'] ?? 'Untitled Module',
                'description'    => $mod['description'] ?? null,
                'order'          => $lastOrder,
                'ai_generated'   => true,
            ]);
        }

        return back()->with('success', count($result['modules']) . ' modul berhasil di-generate.');
    }

    /**
     * Mode B: Generate Lessons untuk sebuah modul (Synchronous)
     */
    public function generateLessons(Course $course, Request $request)
    {
        $validated = $request->validate([
            'module_id'      => 'required|exists:adaptive_modules,id',
            'count'          => 'required|integer|min:1|max:10',
        ]);

        $module = AdaptiveModule::findOrFail($validated['module_id']);
        
        // Ensure module belongs to course
        if ($module->course_id !== $course->id) {
            abort(403);
        }

        $result = $this->aiService->generateLessons($module, $module->archetype_name, $validated['count']);

        if (!$result || !isset($result['lessons'])) {
            return back()->with('error', 'Gagal menghasilkan lesson dari AI. Pastikan server Ollama berjalan.');
        }

        $lastOrder = $module->lessons()->max('order') ?? -1;

        foreach ($result['lessons'] as $les) {
            $lastOrder++;
            $module->lessons()->create([
                'title'        => $les['title'] ?? 'Untitled Lesson',
                'content'      => $les['content'] ?? '<p>No content generated</p>',
                'order'        => $lastOrder,
                'ai_generated' => true,
            ]);
        }

        return back()->with('success', count($result['lessons']) . ' lesson berhasil di-generate untuk modul ini.');
    }

    /**
     * Mode C: Generate Full Curriculum (Asynchronous)
     */
    public function generateFull(Course $course, Request $request)
    {
        $validated = $request->validate([
            'archetype_name' => 'required|string',
            'module_count'   => 'required|integer|min:1|max:5',
            'lesson_count'   => 'required|integer|min:1|max:5',
            'extra_topics'   => 'nullable|string'
        ]);

        $job = new GenerateAdaptiveContentJob(
            $course,
            $validated['archetype_name'],
            $validated['module_count'],
            $validated['lesson_count'],
            $validated['extra_topics']
        );

        app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch($job);
        
        // Cache initial status to ensure the client immediately gets a valid status check
        $jobId = $job->job ? $job->job->getJobId() : null;
        
        // Fallback random ID if connection is sync (for local testing without queue)
        if (!$jobId) {
            $jobId = uniqid('sync_');
            Cache::put("adaptive_ai_job_{$jobId}", ['status' => 'completed', 'message' => 'Job executed synchronously.'], 3600);
        }

        return response()->json([
            'status' => 'queued',
            'job_id' => $jobId
        ]);
    }

    /**
     * Polling Endpoint for Job Status
     */
    public function checkStatus($jobId)
    {
        $cacheKey = "adaptive_ai_job_{$jobId}";
        $statusData = Cache::get($cacheKey);

        if (!$statusData) {
            return response()->json(['status' => 'unknown', 'message' => 'Job status not found or processing.']);
        }

        // If completed or failed, we can clear the cache
        if (in_array($statusData['status'], ['completed', 'failed'])) {
            Cache::forget($cacheKey);
        }

        return response()->json($statusData);
    }
}

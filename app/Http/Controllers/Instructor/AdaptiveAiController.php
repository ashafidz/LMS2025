<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\AiGenerationJob;
use App\Models\AiReference;
use App\Jobs\GenerateAdaptiveContentJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;

class AdaptiveAiController extends Controller
{
    /**
     * Start Background Job to Generate Full Curriculum.
     */
    public function generateFull(Course $course, Request $request)
    {
        $request->validate([
            'archetype_name' => 'required|string',
            'module_count' => 'required|integer|min:1|max:5',
            'lesson_count' => 'required|integer|min:1|max:5',
            'extra_topics' => 'nullable|string'
        ]);

        $jobRecord = AiGenerationJob::create([
            'course_id' => $course->id,
            'archetype_name' => $request->archetype_name,
            'type' => 'full',
            'status' => 'queued',
            'progress' => 0,
            'message' => 'Masuk antrean...'
        ]);

        GenerateAdaptiveContentJob::dispatch($jobRecord, [
            'module_count' => $request->module_count,
            'lesson_count' => $request->lesson_count,
            'extra_topics' => $request->extra_topics
        ]);

        return response()->json([
            'status' => 'queued',
            'job_id' => $jobRecord->id
        ]);
    }

    /**
     * Start Background Job to Generate Modules only.
     */
    public function generateModules(Course $course, Request $request)
    {
        $request->validate([
            'archetype_name' => 'required|string',
            'count' => 'required|integer|min:1|max:10',
            'extra_topics' => 'nullable|string'
        ]);

        $jobRecord = AiGenerationJob::create([
            'course_id' => $course->id,
            'archetype_name' => $request->archetype_name,
            'type' => 'modules',
            'status' => 'queued',
            'progress' => 0,
            'message' => 'Masuk antrean...'
        ]);

        GenerateAdaptiveContentJob::dispatch($jobRecord, [
            'module_count' => $request->count,
            'extra_topics' => $request->extra_topics
        ]);

        return response()->json([
            'status' => 'queued',
            'job_id' => $jobRecord->id
        ]);
    }

    /**
     * Poll the status of a specific job.
     */
    public function checkStatus(Course $course, $jobId)
    {
        $job = AiGenerationJob::where('course_id', $course->id)->findOrFail($jobId);
        
        return response()->json([
            'status' => $job->status, // queued, processing, completed, failed
            'progress' => $job->progress,
            'message' => $job->message,
            'error' => $job->error
        ]);
    }

    /**
     * Start Background Job to Generate Lessons for a specific existing Module.
     */
    public function generateLessons(Course $course, Request $request)
    {
        $request->validate([
            'archetype_name' => 'required|string',
            'module_id'      => 'required|integer|exists:adaptive_modules,id',
            'lesson_count'   => 'required|integer|min:1|max:10',
            'extra_topics'   => 'nullable|string'
        ]);

        $jobRecord = AiGenerationJob::create([
            'course_id'      => $course->id,
            'archetype_name' => $request->archetype_name,
            'type'           => 'lessons',
            'status'         => 'queued',
            'progress'       => 0,
            'message'        => 'Masuk antrean...'
        ]);

        GenerateAdaptiveContentJob::dispatch($jobRecord, [
            'module_id'    => $request->module_id,
            'lesson_count' => $request->lesson_count,
            'extra_topics' => $request->extra_topics
        ]);

        return response()->json([
            'status' => 'queued',
            'job_id' => $jobRecord->id
        ]);
    }

    /**
     * Upload reference file for RAG.
     */
    public function uploadReference(Course $course, Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,txt,md|max:10240', // 10MB max
            'archetype' => 'required|string'
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = $file->getClientOriginalName();
        $path = $file->store('ai_references', 'local');
        
        $extractedText = '';

        try {
            $absolutePath = Storage::disk('local')->path($path);
            
            if ($extension === 'pdf') {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($absolutePath);
                $extractedText = $pdf->getText();
            } else {
                // txt, md
                $extractedText = file_get_contents($absolutePath);
            }
        } catch (\Exception $e) {
            Storage::disk('local')->delete($path);
            return response()->json(['error' => 'Gagal membaca isi file: ' . $e->getMessage()], 422);
        }

        // Save to Database
        $reference = AiReference::create([
            'course_id' => $course->id,
            'archetype_name' => $request->archetype,
            'file_path' => $path,
            'original_filename' => $originalName,
            'extracted_text' => $extractedText,
        ]);

        return response()->json([
            'message' => 'File berhasil diunggah',
            'reference' => $reference
        ]);
    }

    /**
     * Delete reference file.
     */
    public function deleteReference(Course $course, AiReference $reference)
    {
        if ($reference->course_id !== $course->id) {
            abort(403);
        }

        Storage::disk('local')->delete($reference->file_path);
        $reference->delete();

        return response()->json(['message' => 'File dihapus']);
    }
}

<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\AiChatSession;
use App\Models\AiReference;
use App\Services\AdaptiveAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;

class AdaptiveAiController extends Controller
{
    protected AdaptiveAiService $aiService;

    public function __construct(AdaptiveAiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Get or create chat session and load history.
     */
    public function loadChat(Course $course, Request $request)
    {
        $archetype = $request->query('archetype');
        if (!$archetype) {
            return response()->json(['error' => 'Archetype is required'], 400);
        }

        $session = AiChatSession::firstOrCreate(
            ['course_id' => $course->id, 'archetype_name' => $archetype],
            ['status' => 'idle']
        );

        $messages = $session->messages()->orderBy('created_at', 'asc')->get();
        $references = AiReference::where('course_id', $course->id)
                                 ->where(function($q) use ($archetype) {
                                     $q->where('archetype_name', $archetype)
                                       ->orWhereNull('archetype_name');
                                 })->get();

        return response()->json([
            'session_id' => $session->id,
            'messages' => $messages,
            'references' => $references,
        ]);
    }

    /**
     * Send a message to AI and get response.
     */
    public function sendMessage(Course $course, Request $request)
    {
        $request->validate([
            'archetype' => 'required|string',
            'message' => 'required|string'
        ]);

        $session = AiChatSession::firstOrCreate(
            ['course_id' => $course->id, 'archetype_name' => $request->archetype],
            ['status' => 'processing']
        );

        // Fetch references to use as RAG Context
        $references = AiReference::where('course_id', $course->id)
            ->where(function($q) use ($request) {
                $q->where('archetype_name', $request->archetype)
                  ->orWhereNull('archetype_name');
            })->get();

        $ragContexts = [];
        foreach ($references as $ref) {
            $ragContexts[] = "Judul Referensi: {$ref->original_filename}\nIsi:\n" . substr($ref->extracted_text, 0, 10000); // Limit to 10k chars per ref to avoid context overflow
        }

        // Call Service (this is a blocking call, can take 10-30 seconds depending on Ollama)
        $aiResponseText = $this->aiService->sendChatMessage($session, $request->message, $ragContexts);

        if (!$aiResponseText) {
            return response()->json(['error' => 'Failed to get response from AI'], 500);
        }

        $session->update(['status' => 'idle']);

        // Fetch the newly created messages
        $newMessages = $session->messages()->orderBy('created_at', 'desc')->take(2)->get()->reverse()->values();

        return response()->json([
            'messages' => $newMessages
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
        $extension = $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $path = $file->store('ai_references', 'local');
        
        $extractedText = '';

        try {
            if ($extension === 'pdf') {
                $parser = new PdfParser();
                $pdf = $parser->parseFile(storage_path('app/' . $path));
                $extractedText = $pdf->getText();
            } else {
                // txt, md
                $extractedText = file_get_contents(storage_path('app/' . $path));
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

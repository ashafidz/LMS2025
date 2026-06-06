<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KmeansRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiLabelCallbackController extends Controller
{
    public function store(Request $request, KmeansRun $run)
    {
        $secret = env('AI_CALLBACK_SECRET');
        $headerSecret = $request->header('X-Callback-Secret');

        if (!$secret || $headerSecret !== $secret) {
            Log::warning("Unauthorized webhook access attempt for KmeansRun ID: {$run->id}");
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'clusters' => 'required|array',
            'clusters.*.cluster_number' => 'required|integer',
            'clusters.*.name' => 'required|string',
            'clusters.*.description' => 'required|string',
        ]);

        $run->update([
            'ai_labels' => [
                'model' => env('OLLAMA_DEFAULT_MODEL', 'llama3:latest'),
                'generated_at' => now()->toIso8601String(),
                'clusters' => $validated['clusters'],
            ],
            'ai_labeling_status' => 'completed',
            'ai_labeling_completed_at' => now(),
        ]);

        Log::info("Successfully received and saved AI labels for KmeansRun ID: {$run->id}");

        return response()->json(['message' => 'Labels saved successfully']);
    }
}

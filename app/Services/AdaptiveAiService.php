<?php

namespace App\Services;

use App\Models\Course;
use App\Models\AiChatSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdaptiveAiService
{
    private string $ollamaUrl;
    private string $model;

    public function __construct()
    {
        $baseUrl = env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434');
        $this->ollamaUrl = rtrim($baseUrl, '/') . '/api/chat';
        $this->model = env('OLLAMA_DEFAULT_MODEL', 'llama3:latest');
    }

    public const ARCHETYPE_DESCRIPTIONS = [
        'Expert Innovator'       => 'Siswa Expert yang sangat committed ke semua fitur AI personalisasi. Semua preferensi AI bernilai sangat tinggi (>4.5).',
        'Adaptive AI Explorer'   => 'Siswa Expert yang aktif menggunakan semua fitur AI. Preferensi AI secara keseluruhan High namun tidak sekuat Expert Innovator.',
        'Guided Mastery Expert'  => 'Siswa Expert yang tetap suka dibimbing AI secara terstruktur. Guidance dan Adaptivity High, meski Transparency Medium.',
        'Selective AI Partner'   => 'Siswa Expert yang selektif menggunakan AI hanya saat benar-benar butuh. Preferensi AI secara keseluruhan Medium.',
        'Achievement Challenger' => 'Siswa berorientasi nilai dan kompetisi (Performance Goal > Mastery Goal). Menggunakan AI sebagai alat benchmark.',
        'Guided Growth Learner'  => 'Siswa dengan prior knowledge rendah (<75%). Membutuhkan scaffolding dan bimbingan intensif.',
    ];

    /**
     * Build the system prompt based on Course and Archetype.
     */
    public function buildSystemPrompt(Course $course, string $archetype): string
    {
        $desc = self::ARCHETYPE_DESCRIPTIONS[$archetype] ?? '';
        
        return "Kamu adalah AI Co-Pilot untuk membantu instruktur merancang kurikulum adaptif.\n"
             . "Kursus yang sedang dikelola: '{$course->title}'\n"
             . "Deskripsi kursus: " . strip_tags($course->description) . "\n\n"
             . "Target kelompok belajar (Archetype): '{$archetype}'\n"
             . "Profil kelompok: {$desc}\n\n"
             . "Instruksi Khusus:\n"
             . "1. Gunakan bahasa Indonesia yang profesional dan mudah dipahami.\n"
             . "2. Fokus pada pembuatan rancangan modul dan lesson (pelajaran) yang sesuai dengan karakteristik kelompok target.\n"
             . "3. Jangan gunakan format JSON kecuali diminta secara spesifik. Gunakan format teks Markdown yang rapi untuk menyajikan draf silabus atau lesson.\n"
             . "4. Jika instruktur mengunggah referensi materi, gunakan referensi tersebut sebagai dasar untuk merancang konten.";
    }

    /**
     * Send chat request to Ollama.
     */
    public function sendChatMessage(AiChatSession $session, string $userMessage, array $ragContexts = []): ?string
    {
        // 1. Build messages array starting with System Prompt
        $messages = [
            [
                'role' => 'system',
                'content' => $this->buildSystemPrompt($session->course, $session->archetype_name)
            ]
        ];

        // 2. Append Chat History
        $history = $session->messages()->orderBy('created_at', 'asc')->get();
        foreach ($history as $msg) {
            if ($msg->role === 'system') continue; // We already set the initial system prompt
            $messages[] = [
                'role' => $msg->role,
                'content' => $msg->content
            ];
        }

        // 3. Inject RAG Context into the user's latest message if available
        $finalUserContent = $userMessage;
        if (!empty($ragContexts)) {
            $contextText = implode("\n\n---\n\n", $ragContexts);
            $finalUserContent = "Berikut adalah materi referensi yang relevan:\n\n{$contextText}\n\nInstruksi saya: {$userMessage}";
        }

        // Add the user's new message to the history payload
        $messages[] = [
            'role' => 'user',
            'content' => $finalUserContent
        ];

        // Save User Message to DB
        $session->messages()->create([
            'role' => 'user',
            'content' => $finalUserContent
        ]);

        // 4. Call Ollama Chat API
        try {
            $response = Http::timeout(300)->post($this->ollamaUrl, [
                'model'    => $this->model,
                'messages' => $messages,
                'stream'   => false,
            ]);

            if ($response->successful()) {
                $assistantContent = $response->json('message.content');
                
                // Save Assistant Message to DB
                $session->messages()->create([
                    'role' => 'assistant',
                    'content' => $assistantContent
                ]);

                return $assistantContent;
            }
            
            Log::error('Ollama Chat Failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('Ollama Connection Error', ['message' => $e->getMessage()]);
            return null;
        }
    }
}

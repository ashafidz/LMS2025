<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdaptiveAiService
{
    private string $ollamaUrl;
    private string $model;

    public function __construct()
    {
        // Using chat API instead of generate for better context handling
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

    public function generateCurriculum(
        Course $course, 
        string $archetype, 
        int $moduleCount, 
        int $lessonCount = 0, // 0 means modules only
        ?string $extraTopics = null,
        array $ragContexts = [] // array of reference texts
    ): ?array {
        $desc = self::ARCHETYPE_DESCRIPTIONS[$archetype] ?? '';

        $systemPrompt = "Kamu adalah instruktur ahli yang membuat kurikulum adaptif. Output WAJIB dalam format JSON murni.\n"
            . "Kursus: '{$course->title}'\n"
            . "Deskripsi kursus: " . strip_tags($course->description) . "\n\n"
            . "Target kelompok belajar (Archetype): '{$archetype}'\n"
            . "Profil kelompok: {$desc}\n"
            . "PENTING: Konten 'lesson' (materi pelajaran) HARUS ditulis dengan sangat detail, panjang, dan komprehensif. "
            . "Minimal 4-5 paragraf per lesson. Berikan penjelasan mendalam, contoh kasus, dan langkah-langkah konkret. Jangan hanya memberikan ringkasan singkat.\n";

        if (!empty($ragContexts)) {
            $systemPrompt .= "\n--- REFERENSI MATERI (RAG) ---\nInstruktur mengunggah referensi berikut. Ekstrak materi yang relevan dan tuliskan penjelasannya secara lengkap dan mendalam:\n\n";
            $systemPrompt .= implode("\n\n=== BATAS REFERENSI ===\n\n", $ragContexts);
            $systemPrompt .= "\n--- AKHIR REFERENSI ---\n";
        }

        $userPrompt = "Tolong buatkan kurikulum dengan {$moduleCount} modul.";
        if ($lessonCount > 0) {
            $userPrompt .= " Setiap modul harus memiliki tepat {$lessonCount} lesson.";
            $userPrompt .= " Ingat, isi 'content' dari setiap lesson harus panjang dan sangat detail (gunakan HTML tag seperti <p>, <strong>, <ul>, <li>).";
        } else {
            $userPrompt .= " Jangan buat lesson di dalamnya.";
        }
        if ($extraTopics) {
            $userPrompt .= " Fokus tambahan/topik spesifik: {$extraTopics}.";
        }

        // Schema JSON for output formatting
        if ($lessonCount > 0) {
            $jsonSchema = '{
                "curriculum": [
                    {
                        "title": "Judul Modul 1",
                        "description": "Deskripsi Modul",
                        "lessons": [
                            {
                                "title": "Judul Lesson 1",
                                "content": "<p>Paragraf pertama yang sangat panjang dan mendetail...</p><p>Paragraf kedua menjelaskan konsep lebih dalam...</p><ul><li>Poin penting 1</li><li>Poin penting 2</li></ul><p>Paragraf penutup dengan kesimpulan yang jelas...</p>"
                            }
                        ]
                    }
                ]
            }';
        } else {
            $jsonSchema = '{
                "curriculum": [
                    {
                        "title": "Judul Modul 1",
                        "description": "Deskripsi Modul"
                    }
                ]
            }';
        }

        $userPrompt .= "\n\nFormat keluaran HARUS berformat JSON seperti contoh berikut:\n$jsonSchema";

        try {
            $response = Http::timeout(600)->post($this->ollamaUrl, [
                'model'    => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt]
                ],
                'stream'   => false,
                'format'   => 'json' // Force Ollama to return JSON if supported by model
            ]);

            if ($response->successful()) {
                $content = $response->json('message.content');
                
                // Coba bersihkan markdown json block jika AI bandel
                $content = preg_replace('/```json/i', '', $content);
                $content = preg_replace('/```/', '', $content);
                $content = trim($content);

                return json_decode($content, true);
            }

            Log::error('Ollama Generation Failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('Ollama Generation Error', ['message' => $e->getMessage()]);
            return null;
        }
    }
}

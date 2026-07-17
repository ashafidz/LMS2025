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

    /**
     * Generate only module structures (titles and descriptions)
     */
    public function generateModules(
        Course $course, 
        int $moduleCount, 
        ?string $extraTopics = null,
        array $ragContexts = []
    ): ?array {
        $systemPrompt = "Kamu adalah instruktur ahli yang membuat kurikulum adaptif. Output WAJIB dalam format JSON murni.\n"
            . "Kursus: '{$course->title}'\n"
            . "Deskripsi kursus: " . strip_tags($course->description) . "\n\n"
            . "Buatlah struktur modul 'Master' yang nantinya bisa di-assign ke berbagai kelompok belajar.\n";

        if (!empty($ragContexts)) {
            $systemPrompt .= "\n--- REFERENSI MATERI (RAG) ---\nInstruktur mengunggah referensi berikut. Ekstrak informasi relevan untuk membantu membentuk struktur kurikulum:\n\n";
            $systemPrompt .= implode("\n\n=== BATAS REFERENSI ===\n\n", $ragContexts);
            $systemPrompt .= "\n--- AKHIR REFERENSI ---\n";
        }

        $userPrompt = "Tolong buatkan struktur kurikulum dengan tepat {$moduleCount} modul. Jangan buat konten materinya, hanya judul dan deskripsi per modul.";
        if ($extraTopics) {
            $userPrompt .= " Fokus tambahan/topik spesifik: {$extraTopics}.";
        }

        $jsonSchema = '{
            "modules": [
                {
                    "title": "Judul Modul 1",
                    "description": "Deskripsi singkat modul ini (1-2 kalimat)"
                }
            ]
        }';

        $userPrompt .= "\n\nFormat keluaran HARUS berformat JSON seperti contoh berikut:\n$jsonSchema";

        try {
            $response = Http::timeout(600)->post($this->ollamaUrl, [
                'model'    => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt]
                ],
                'stream'   => false,
                'format'   => 'json'
            ]);

            if ($response->successful()) {
                $content = preg_replace('/```json/i', '', $response->json('message.content'));
                $content = trim(preg_replace('/```/', '', $content));
                return json_decode($content, true);
            }

            Log::error('Ollama Module Generation Failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('Ollama Module Generation Error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Generate only lessons for a specific existing module.
     */
    public function generateLessonsForModule(
        Course $course,
        \App\Models\AdaptiveModule $module,
        int $lessonCount,
        ?string $extraTopics = null,
        array $ragContexts = []
    ): ?array {
        $systemPrompt = "Kamu adalah instruktur ahli yang membuat materi pelajaran (lesson). Output WAJIB dalam format JSON murni.\n"
            . "Kursus: '{$course->title}'\n"
            . "Modul target: '{$module->title}'\n"
            . "Deskripsi modul: " . ($module->description ?? 'Tidak ada deskripsi') . "\n"
            . "PENTING: Konten setiap lesson HARUS sangat detail dan komprehensif. Minimal 4-5 paragraf. "
            . "Berikan penjelasan mendalam, contoh konkret, dan langkah-langkah praktis.\n";

        if (!empty($ragContexts)) {
            $systemPrompt .= "\n--- REFERENSI MATERI ---\n";
            $systemPrompt .= implode("\n\n=== BATAS REFERENSI ===\n\n", $ragContexts);
            $systemPrompt .= "\n--- AKHIR REFERENSI ---\n";
        }

        $userPrompt = "Buatkan {$lessonCount} lesson untuk modul '{$module->title}'.";
        if ($extraTopics) {
            $userPrompt .= " Fokus tambahan: {$extraTopics}.";
        }
        $userPrompt .= " Isi 'content' harus panjang (gunakan HTML: <p>, <strong>, <ul>, <li>).\n\n";
        $userPrompt .= "Format keluaran JSON:\n"
            . '{"lessons": [{"title": "Judul Lesson", "content": "<p>Konten panjang...</p>"}]}';

        try {
            $response = Http::timeout(600)->post($this->ollamaUrl, [
                'model'    => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userPrompt]
                ],
                'stream'   => false,
                'format'   => 'json'
            ]);

            if ($response->successful()) {
                $content = preg_replace('/```json/i', '', $response->json('message.content'));
                $content = trim(preg_replace('/```/', '', $content));
                return json_decode($content, true);
            }

            Log::error('Ollama Lessons Generation Failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('Ollama Lessons Generation Error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Generate assignment-type lessons for a specific existing module.
     */
    public function generateAssignmentLessons(
        Course $course,
        \App\Models\AdaptiveModule $module,
        int $lessonCount,
        ?string $extraTopics = null,
        array $ragContexts = []
    ): ?array {
        $systemPrompt = "Kamu adalah instruktur ahli yang merancang tugas/penugasan (assignment) untuk kursus. Output WAJIB dalam format JSON murni.\n"
            . "Kursus: '{$course->title}'\n"
            . "Modul target: '{$module->title}'\n"
            . "Deskripsi modul: " . ($module->description ?? 'Tidak ada deskripsi') . "\n"
            . "Kamu harus merancang tugas yang:\n"
            . "- Relevan dengan topik modul\n"
            . "- Memiliki instruksi yang SANGAT JELAS dan DETAIL (langkah-langkah pengerjaan, format pengumpulan, dll)\n"
            . "- Disesuaikan dengan karakteristik kelompok belajar\n"
            . "- Memiliki kriteria penilaian yang terukur\n";

        if (!empty($ragContexts)) {
            $systemPrompt .= "\n--- REFERENSI MATERI ---\n";
            $systemPrompt .= implode("\n\n=== BATAS REFERENSI ===\n\n", $ragContexts);
            $systemPrompt .= "\n--- AKHIR REFERENSI ---\n";
        }

        $userPrompt = "Buatkan {$lessonCount} penugasan untuk modul '{$module->title}'.";
        if ($extraTopics) {
            $userPrompt .= " Fokus tambahan: {$extraTopics}.";
        }
        $userPrompt .= "\n\nSetiap penugasan harus memiliki:\n"
            . "- 'title': Judul penugasan yang deskriptif\n"
            . "- 'description': Deskripsi singkat tujuan penugasan (HTML, 1-2 paragraf)\n"
            . "- 'instructions': Instruksi pengerjaan yang sangat detail (HTML: <p>, <ol>, <li>, <strong>). "
            . "  Sertakan: latar belakang, langkah-langkah, format pengumpulan, dan kriteria penilaian.\n"
            . "- 'max_score': Nilai maksimum (angka, misal 100)\n\n"
            . "Format keluaran JSON:\n"
            . '{"assignments": [{"title": "Judul Penugasan", "description": "<p>Deskripsi...</p>", "instructions": "<p>Latar belakang...</p><ol><li>Langkah 1...</li></ol>", "max_score": 100}]}';

        try {
            $response = Http::timeout(600)->post($this->ollamaUrl, [
                'model'    => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userPrompt]
                ],
                'stream'   => false,
                'format'   => 'json'
            ]);

            if ($response->successful()) {
                $content = preg_replace('/```json/i', '', $response->json('message.content'));
                $content = trim(preg_replace('/```/', '', $content));
                return json_decode($content, true);
            }

            Log::error('Ollama Assignment Generation Failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('Ollama Assignment Generation Error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Generate quiz-type lessons for a specific existing module.
     */
    public function generateQuizLessons(
        Course $course,
        \App\Models\AdaptiveModule $module,
        int $lessonCount, // For quizzes, this is usually 1, but we support multiple
        ?string $extraTopics = null,
        array $ragContexts = []
    ): ?array {
        $systemPrompt = "Kamu adalah instruktur ahli yang merancang soal Quiz untuk kursus. Output WAJIB dalam format JSON murni.\n"
            . "Kursus: '{$course->title}'\n"
            . "Modul target: '{$module->title}'\n"
            . "Deskripsi modul: " . ($module->description ?? 'Tidak ada deskripsi') . "\n"
            . "Kamu harus merancang quiz berupa pilihan ganda (Multiple Choice) yang:\n"
            . "- Menguji pemahaman konsep-konsep kunci pada modul tersebut\n"
            . "- Disesuaikan tingkat kesulitannya dengan kelompok belajar\n"
            . "- Terdiri dari 5-10 pertanyaan per quiz\n";

        if (!empty($ragContexts)) {
            $systemPrompt .= "\n--- REFERENSI MATERI ---\n";
            $systemPrompt .= implode("\n\n=== BATAS REFERENSI ===\n\n", $ragContexts);
            $systemPrompt .= "\n--- AKHIR REFERENSI ---\n";
        }

        $userPrompt = "Buatkan {$lessonCount} modul quiz untuk modul '{$module->title}'.";
        if ($extraTopics) {
            $userPrompt .= " Fokus tambahan quiz: {$extraTopics}.";
        }
        $userPrompt .= "\n\nSetiap quiz harus memiliki:\n"
            . "- 'title': Judul quiz (misalnya: Quiz Pemahaman Modul 1)\n"
            . "- 'description': Deskripsi atau instruksi singkat quiz (HTML, 1 paragraf)\n"
            . "- 'questions': Array dari objek pertanyaan yang berisi:\n"
            . "    - 'question_text': Teks pertanyaan\n"
            . "    - 'options': Array yang berisi tepat 4 pilihan jawaban (string)\n"
            . "    - 'correct_answer_index': Index pilihan jawaban yang benar (0 sampai 3)\n"
            . "    - 'explanation': Penjelasan singkat mengapa jawaban tersebut benar\n\n"
            . "Format keluaran JSON:\n"
            . '{"quizzes": [{"title": "Quiz...", "description": "<p>...</p>", "questions": [{"question_text": "Apa...", "options": ["A", "B", "C", "D"], "correct_answer_index": 0, "explanation": "Karena..."}]}]}';

        try {
            $response = Http::timeout(600)->post($this->ollamaUrl, [
                'model'    => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userPrompt]
                ],
                'stream'   => false,
                'format'   => 'json'
            ]);

            if ($response->successful()) {
                $content = preg_replace('/```json/i', '', $response->json('message.content'));
                $content = trim(preg_replace('/```/', '', $content));
                return json_decode($content, true);
            }

            Log::error('Ollama Quiz Generation Failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('Ollama Quiz Generation Error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function recommendArchetypes(\App\Models\AdaptiveModule $module): ?array
    {
        $systemPrompt = "Kamu adalah sistem pakar pendidikan AI. Tugasmu adalah menganalisis modul pembelajaran dan merekomendasikan profil siswa (archetype) mana yang paling cocok dengan modul tersebut.\n\n"
            . "Daftar Archetype yang tersedia:\n";
        
        foreach (\App\Http\Controllers\Instructor\AdaptiveContentController::ARCHETYPES as $name => $desc) {
            $systemPrompt .= "- {$name}: {$desc}\n";
        }
        
        $systemPrompt .= "\nOutput WAJIB dalam format JSON berupa array nama archetype yang direkomendasikan.\n"
            . 'Contoh: {"recommended_archetypes": ["Expert Innovator", "Adaptive AI Explorer"]}';

        $userPrompt = "Judul Modul: {$module->title}\n"
            . "Deskripsi Modul: {$module->description}\n\n"
            . "Daftar Materi (Lessons) di dalam modul ini:\n";
            
        foreach ($module->lessons as $lesson) {
            $userPrompt .= "- [{$lesson->lesson_type}] {$lesson->title}\n";
            if (in_array($lesson->lesson_type, ['article', 'assignment'])) {
                $contentLimit = substr(strip_tags($lesson->content), 0, 500); // Limit content to avoid massive prompts
                $userPrompt .= "  Konten / Instruksi: " . $contentLimit . "...\n";
            }
        }

        try {
            $response = Http::timeout(120)->post($this->ollamaUrl, [
                'model'    => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userPrompt]
                ],
                'stream'   => false,
                'format'   => 'json'
            ]);

            if ($response->successful()) {
                $content = preg_replace('/```json/i', '', $response->json('message.content'));
                $content = trim(preg_replace('/```/', '', $content));
                return json_decode($content, true);
            }

            Log::error('Ollama Recommend Archetypes Failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('Ollama Recommend Archetypes Error', ['message' => $e->getMessage()]);
            return null;
        }
    }
}

<?php

namespace App\Services;

use App\Models\Course;
use App\Models\AdaptiveModule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdaptiveAiService
{
    private string $ollamaUrl = 'http://127.0.0.1:11434/api/generate';
    private string $model = 'llama3:latest'; // or whatever is configured

    public const ARCHETYPE_DESCRIPTIONS = [
        'Expert Innovator'       => 'Siswa Expert yang sangat committed ke semua fitur AI personalisasi. Semua preferensi AI bernilai sangat tinggi (>4.5).',
        'Adaptive AI Explorer'   => 'Siswa Expert yang aktif menggunakan semua fitur AI. Preferensi AI secara keseluruhan High namun tidak sekuat Expert Innovator.',
        'Guided Mastery Expert'  => 'Siswa Expert yang tetap suka dibimbing AI secara terstruktur. Guidance dan Adaptivity High, meski Transparency Medium.',
        'Selective AI Partner'   => 'Siswa Expert yang selektif menggunakan AI hanya saat benar-benar butuh. Preferensi AI secara keseluruhan Medium.',
        'Achievement Challenger' => 'Siswa berorientasi nilai dan kompetisi (Performance Goal > Mastery Goal). Menggunakan AI sebagai alat benchmark.',
        'Guided Growth Learner'  => 'Siswa dengan prior knowledge rendah (<75%). Membutuhkan scaffolding dan bimbingan intensif.',
    ];

    /**
     * Call Ollama directly and parse JSON response.
     */
    private function callOllama(string $prompt): ?array
    {
        try {
            $response = Http::timeout(120)->post($this->ollamaUrl, [
                'model'  => $this->model,
                'prompt' => $prompt,
                'format' => 'json',
                'stream' => false,
            ]);

            if ($response->successful()) {
                $content = $response->json('response');
                return json_decode($content, true);
            }
            
            Log::error('Ollama Generation Failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('Ollama Connection Error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Mode A: Generate Modul Saja
     */
    public function generateModules(Course $course, string $archetype, int $count, ?string $extraTopics = null): ?array
    {
        $desc = self::ARCHETYPE_DESCRIPTIONS[$archetype] ?? '';
        
        $prompt = "Kamu adalah instructional designer. Buatkan {$count} modul untuk kursus '{$course->title}'.\n"
                . "Deskripsi kursus: " . strip_tags($course->description) . "\n\n"
                . "Modul ini ditujukan untuk kelompok belajar bertipe '{$archetype}'.\n"
                . "Profil kelompok: {$desc}\n";

        if ($extraTopics) {
            $prompt .= "\nTopik tambahan yang harus dicover: {$extraTopics}\n";
        }

        $prompt .= "\nSetiap modul harus memiliki judul dan deskripsi singkat yang sesuai dengan gaya belajar kelompok ini.\n"
                 . "Output WAJIB dalam format JSON yang valid:\n"
                 . "{\n"
                 . "  \"modules\": [\n"
                 . "    { \"title\": \"...\", \"description\": \"...\" }\n"
                 . "  ]\n"
                 . "}";

        return $this->callOllama($prompt);
    }

    /**
     * Mode B: Generate Lesson dalam Modul
     */
    public function generateLessons(AdaptiveModule $module, string $archetype, int $count): ?array
    {
        $desc = self::ARCHETYPE_DESCRIPTIONS[$archetype] ?? '';
        $courseTitle = $module->course->title ?? 'Kursus';
        
        $prompt = "Kamu adalah instructional designer. Buatkan {$count} lesson (pelajaran) untuk modul '{$module->title}' dalam kursus '{$courseTitle}'.\n"
                . "Deskripsi modul: {$module->description}\n\n"
                . "Kelompok target siswa: '{$archetype}'\n"
                . "Profil kelompok: {$desc}\n\n"
                . "Setiap lesson harus memiliki judul dan konten artikel lengkap (minimal 3 paragraf). Konten artikel HARUS menggunakan format HTML (seperti <h2>, <p>, <ul>).\n"
                . "Output WAJIB dalam format JSON yang valid:\n"
                . "{\n"
                . "  \"lessons\": [\n"
                . "    { \"title\": \"...\", \"content\": \"<h2>...</h2><p>...</p>...\" }\n"
                . "  ]\n"
                . "}";

        return $this->callOllama($prompt);
    }

    /**
     * Mode C: Generate Full Curriculum (Modules + Lessons)
     * This is usually called from a Job because it can take a long time.
     */
    public function generateFull(Course $course, string $archetype, int $moduleCount, int $lessonCount, ?string $extraTopics = null): ?array
    {
        // For full curriculum, we can either do it in one big prompt or sequentially.
        // Sequential is safer for Ollama JSON formatting limits.
        
        $modulesResult = $this->generateModules($course, $archetype, $moduleCount, $extraTopics);
        if (!$modulesResult || !isset($modulesResult['modules'])) {
            return null;
        }

        $fullCurriculum = [];

        foreach ($modulesResult['modules'] as $modData) {
            $modTitle = $modData['title'] ?? 'Untitled Module';
            $modDesc = $modData['description'] ?? '';

            // Simulate a module object for the generateLessons prompt
            $tempModule = new AdaptiveModule(['title' => $modTitle, 'description' => $modDesc]);
            $tempModule->setRelation('course', $course);

            $lessonsResult = $this->generateLessons($tempModule, $archetype, $lessonCount);

            $fullCurriculum[] = [
                'title' => $modTitle,
                'description' => $modDesc,
                'lessons' => $lessonsResult['lessons'] ?? []
            ];
        }

        return ['curriculum' => $fullCurriculum];
    }
}

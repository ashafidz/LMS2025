<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class EmbedEducationalTheory extends Command
{
    protected $signature = 'lms:embed-theory';
    protected $description = 'Embed educational theory documents into Qdrant using Ollama';

    public function getTheories(): array
    {
        $markdownFile = base_path('docs/EmbedEducational.md');
        if (!file_exists($markdownFile)) {
            $this->error("File markdown teori tidak ditemukan: {$markdownFile}");
            return [];
        }

        $content = file_get_contents($markdownFile);
        $sections = explode('## Archetype:', $content);
        
        // Buang bagian pertama (judul/intro)
        array_shift($sections);
        
        $theories = [];
        foreach ($sections as $index => $section) {
            $lines = explode("\n", trim($section));
            $title = trim($lines[0]);
            
            // Gabungkan sisanya menjadi konten
            array_shift($lines);
            $body = trim(implode("\n", $lines));
            
            $theories[] = [
                'id' => $index + 1,
                'title' => "Archetype: " . $title,
                'content' => $body
            ];
        }

        return $theories;
    }

    public function handle()
    {
        $ollamaUrl = env('OLLAMA_BASE_URL', 'http://192.168.0.223:11434');
        $qdrantUrl = env('QDRANT_BASE_URL', 'http://192.168.0.223:6333');
        $collection = env('QDRANT_THEORY_COLLECTION', 'educational_theory');

        $this->info("Creating collection '{$collection}' in Qdrant...");

        // Recreate collection (768 dimensions for nomic-embed-text)
        Http::delete("{$qdrantUrl}/collections/{$collection}");
        $createRes = Http::put("{$qdrantUrl}/collections/{$collection}", [
            'vectors' => [
                'size' => 768,
                'distance' => 'Cosine'
            ]
        ]);

        if (!$createRes->successful()) {
            $this->error('Failed to create Qdrant collection: ' . $createRes->body());
            return;
        }

        $points = [];

        $theories = $this->getTheories();
        if (empty($theories)) {
            return;
        }

        $this->info("Embedding theories...");
        foreach ($theories as $theory) {
            $embedRes = Http::post("{$ollamaUrl}/api/embeddings", [
                'model' => 'nomic-embed-text:latest',
                'prompt' => $theory['content']
            ]);

            if ($embedRes->successful()) {
                $vector = $embedRes->json('embedding');
                $points[] = [
                    'id' => $theory['id'],
                    'vector' => $vector,
                    'payload' => [
                        'title' => $theory['title'],
                        'content' => $theory['content']
                    ]
                ];
                $this->info("Embedded: {$theory['title']}");
            } else {
                $this->error("Failed to embed {$theory['title']}: " . $embedRes->body());
            }
        }

        $this->info("Uploading points to Qdrant...");
        $upsertRes = Http::put("{$qdrantUrl}/collections/{$collection}/points", [
            'points' => $points
        ]);

        if ($upsertRes->successful()) {
            $this->info("Successfully embedded " . count($points) . " theories to Qdrant.");
        } else {
            $this->error("Failed to upload to Qdrant: " . $upsertRes->body());
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class EmbedEducationalTheory extends Command
{
    protected $signature = 'lms:embed-theory';
    protected $description = 'Embed educational theory documents into Qdrant using Ollama';

    private array $theories = [
        [
            'id' => 1,
            'title' => 'Independent Mastery Explorer',
            'content' => 'Description: A learner who is intrinsically motivated to master knowledge, prefers self-directed learning, demonstrates high confidence, and requires minimal external guidance. Profiling Pattern: Mastery Goal. Knowledge State: Intermediate to Expert. SDT Profile: Autonomy High, Competence High, Relatedness Low. Human-AI Interaction: Guidance Low, Adaptivity High, Transparency Medium to High. AI Mentor Strategy: Socratic questioning, Reflective prompts, Minimal direct instruction, Challenge-oriented feedback.'
        ],
        [
            'id' => 2,
            'title' => 'Collaborative Mastery Builder',
            'content' => 'Description: A learner who seeks mastery through interaction, collaboration, and shared problem solving. Profiling Pattern: Mastery Goal. Knowledge State: Beginner to Intermediate. SDT Profile: Autonomy Medium, Competence Medium, Relatedness High. Human-AI Interaction: Guidance High, Transparency High. AI Mentor Strategy: Guided mentoring, Scaffolded feedback, Collaborative prompts.'
        ],
        [
            'id' => 3,
            'title' => 'Achievement Challenger',
            'content' => 'Description: A learner driven by performance, competition, achievement, and measurable success. Profiling Pattern: Performance Goal. Knowledge State: Intermediate to Expert. SDT Profile: Competence High, Relatedness Low to Medium. Human-AI Interaction: Feedback High, Guidance Medium. AI Mentor Strategy: Performance coaching, Competitive feedback, Benchmark comparison.'
        ],
        [
            'id' => 4,
            'title' => 'Guided Growth Learner',
            'content' => 'Description: A learner with limited prior knowledge who requires strong scaffolding and structured guidance. Profiling Pattern: Mastery Goal. Knowledge State: Novice to Beginner. SDT Profile: Autonomy Low, Competence Low. Human-AI Interaction: Guidance High, Feedback High. AI Mentor Strategy: Step-by-step tutoring, Frequent hints, Positive reinforcement.'
        ],
        [
            'id' => 5,
            'title' => 'Adaptive AI Explorer',
            'content' => 'Description: A learner who actively embraces AI-supported learning and values personalization. Profiling Pattern: Mastery Goal. Knowledge State: Intermediate. SDT Profile: Autonomy High. Human-AI Interaction: Transparency High, Adaptivity High, Feedback Medium. AI Mentor Strategy: Adaptive coaching, Personalized recommendations.'
        ],
        [
            'id' => 6,
            'title' => 'Strategic Performer',
            'content' => 'Description: A learner who optimizes effort and learning strategies to maximize performance outcomes. Profiling Pattern: Performance Goal. Knowledge State: Intermediate. SDT Profile: Competence High, Autonomy Medium. Human-AI Interaction: Feedback High. AI Mentor Strategy: Performance optimization coaching.'
        ],
        [
            'id' => 7,
            'title' => 'Social AI Learner',
            'content' => 'Description: A learner who combines social interaction and AI support to enhance learning. Profiling Pattern: Mastery or Performance Goal. Knowledge State: Beginner to Intermediate. SDT Profile: Relatedness High. Human-AI Interaction: Guidance High, Transparency High. AI Mentor Strategy: Collaborative mentoring, Discussion facilitation.'
        ],
        [
            'id' => 8,
            'title' => 'Expert Innovator',
            'content' => 'Description: A highly competent learner who uses AI as a thinking partner to explore advanced concepts and innovation opportunities. Profiling Pattern: Mastery Goal. Knowledge State: Expert. SDT Profile: Autonomy High, Competence High. Human-AI Interaction: Adaptivity High, Guidance Low. AI Mentor Strategy: Innovation coaching, Critical questioning.'
        ]
    ];

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

        $this->info("Embedding theories...");
        foreach ($this->theories as $theory) {
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

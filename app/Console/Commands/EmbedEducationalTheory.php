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
            'title' => 'Self-Determination Theory (SDT)',
            'content' => 'Self-Determination Theory (SDT) oleh Deci & Ryan menekankan pentingnya motivasi intrinsik. Tiga kebutuhan dasar psikologis yang harus dipenuhi adalah: 1) Autonomy (Kemandirian): Perasaan memiliki pilihan dan kontrol atas tindakan sendiri. Pelajar dengan otonomi tinggi lebih suka belajar mandiri dan mengeksplorasi materi tanpa terlalu banyak panduan. 2) Competence (Kompetensi): Perasaan mampu dan efektif dalam melakukan tugas. Pelajar butuh tantangan yang sesuai dengan kemampuan mereka. 3) Relatedness (Keterkaitan): Perasaan terhubung dengan orang lain, termasuk pengajar dan teman sebaya. Kebutuhan ini mendorong kolaborasi dan interaksi sosial dalam pembelajaran.'
        ],
        [
            'id' => 2,
            'title' => 'Goal Orientation Theory',
            'content' => 'Goal Orientation Theory membedakan dua orientasi utama dalam belajar: 1) Mastery Goal Orientation: Fokus pada penguasaan materi, pemahaman mendalam, dan pengembangan keterampilan. Siswa dengan orientasi ini tidak takut salah karena melihat kesalahan sebagai bagian dari proses belajar. Mereka membutuhkan penjelasan detail dan alasan di balik sebuah konsep. 2) Performance Goal Orientation: Fokus pada pencapaian nilai tinggi, menyelesaikan tugas secepat mungkin, dan terlihat kompeten di depan orang lain. Siswa ini membutuhkan panduan yang sangat jelas (high guidance), ringkasan materi, dan langkah-langkah praktis untuk menyelesaikan ujian atau tugas.'
        ],
        [
            'id' => 3,
            'title' => 'Prior Knowledge and Bloom Taxonomy',
            'content' => 'Prior Knowledge (Pengetahuan Awal) adalah prediktor terkuat untuk keberhasilan belajar. Jika pengetahuan awal rendah (Low Prior Knowledge), siswa berada pada tingkat bawah Bloom Taxonomy (Mengingat dan Memahami). Mereka membutuhkan analogi sederhana, penjelasan langkah demi langkah, dan definisi dasar. Jika pengetahuan awal tinggi (High Prior Knowledge), siswa berada pada tingkat atas (Menganalisis, Mengevaluasi, Mencipta). Mereka akan bosan jika diberi materi dasar dan lebih cocok diberikan studi kasus kompleks, pemecahan masalah, atau cukup review singkat.'
        ],
        [
            'id' => 4,
            'title' => 'AI Preference and Adaptivity',
            'content' => 'Preferensi siswa terhadap AI Mentor terbagi dalam beberapa dimensi: 1) Transparency: Seberapa detail AI harus menjelaskan proses penalarannya. 2) Guidance: Seberapa presisi AI harus memandu siswa (langsung memberi jawaban vs memberi petunjuk agar siswa berpikir sendiri). Siswa dengan otonomi tinggi biasanya lebih suka panduan rendah (low guidance). 3) Feedback: Jenis umpan balik (korektif, evaluatif, atau reflektif). AI harus mengadaptasi sistem prompt-nya agar sesuai dengan kombinasi profil motivasi (SDT), orientasi tujuan, dan tingkat pengetahuan siswa.'
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

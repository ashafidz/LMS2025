<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProfilingComponent;
use App\Models\ProfilingDimension;

class ProfilingComponentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Goal Setting
        $goalSetting = ProfilingComponent::firstOrCreate(
            ['name' => 'Goal Setting'],
            [
                'description' => 'Mengukur orientasi tujuan student dalam mengikuti kursus',
                'theory_reference' => 'Achievement Goal Theory',
                'mechanics_type' => 'likert',
                'scale_min' => 1,
                'scale_max' => 5,
                'order' => 1,
            ]
        );

        $goalDimensions = [
            ['name' => 'Mastery Goal', 'description' => 'Ingin memahami dan menguasai materi', 'order' => 1],
            ['name' => 'Performance Goal', 'description' => 'Ingin nilai/pengakuan', 'order' => 2],
        ];

        foreach ($goalDimensions as $dim) {
            ProfilingDimension::firstOrCreate(
                ['component_id' => $goalSetting->id, 'name' => $dim['name']],
                $dim
            );
        }

        // Component 2 (Prior Knowledge) is course-specific, so it's not seeded here.
        // It has a placeholder in the sequence so the actual components are 1, 3, 4.

        // 3. Motivational Profile
        $sdt = ProfilingComponent::firstOrCreate(
            ['name' => 'Motivational Profile'],
            [
                'description' => 'Mengukur motivasi intrinsik dan ekstrinsik student',
                'theory_reference' => 'Self-Determination Theory (SDT)',
                'mechanics_type' => 'likert',
                'scale_min' => 1,
                'scale_max' => 5,
                'order' => 3, // Skips 2 for Prior Knowledge
            ]
        );

        $sdtDimensions = [
            ['name' => 'Autonomy', 'description' => 'Kebutuhan akan kemandirian dalam belajar', 'order' => 1],
            ['name' => 'Competence', 'description' => 'Kebutuhan merasa mampu menyelesaikan tugas', 'order' => 2],
            ['name' => 'Relatedness', 'description' => 'Kebutuhan merasa terhubung dengan orang lain', 'order' => 3],
        ];

        foreach ($sdtDimensions as $dim) {
            ProfilingDimension::firstOrCreate(
                ['component_id' => $sdt->id, 'name' => $dim['name']],
                $dim
            );
        }

        // 4. AI Interaction Preference
        $aiPreference = ProfilingComponent::firstOrCreate(
            ['name' => 'AI Interaction Preference'],
            [
                'description' => 'Mengukur preferensi student terhadap bantuan AI dalam kursus',
                'theory_reference' => 'AI Interaction Framework',
                'mechanics_type' => 'likert',
                'scale_min' => 1,
                'scale_max' => 5,
                'order' => 4,
            ]
        );

        $aiDimensions = [
            ['name' => 'Transparency', 'description' => 'Preferensi terhadap penjelasan alasan AI', 'order' => 1],
            ['name' => 'Guidance', 'description' => 'Preferensi terhadap instruksi yang spesifik dari AI', 'order' => 2],
            ['name' => 'Adaptivity', 'description' => 'Preferensi terhadap penyesuaian konten otomatis', 'order' => 3],
            ['name' => 'Feedback', 'description' => 'Preferensi terhadap frekuensi feedback AI', 'order' => 4],
        ];

        foreach ($aiDimensions as $dim) {
            ProfilingDimension::firstOrCreate(
                ['component_id' => $aiPreference->id, 'name' => $dim['name']],
                $dim
            );
        }
    }
}

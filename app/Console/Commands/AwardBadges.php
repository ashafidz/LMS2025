<?php

namespace App\Console\Commands;

use App\Models\Badge;
use App\Services\BadgeService;
use Illuminate\Console\Command;

class AwardBadges extends Command
{
    protected $signature = 'badges:award {--badge= : ID badge tertentu (kosong = semua badge aktif)}';

    protected $description = 'Beri badge ke semua siswa yang sudah memenuhi syaratnya (jaring pengaman harian)';

    public function handle(): int
    {
        $badges = Badge::where('is_active', true)
            ->whereHas('rules')
            ->when($this->option('badge'), fn ($q, $id) => $q->whereKey($id))
            ->get();

        foreach ($badges as $badge) {
            $granted = BadgeService::backfill($badge);
            $this->line("{$badge->title}: {$granted} siswa baru");
        }

        $this->info("Selesai memeriksa {$badges->count()} badge.");

        return self::SUCCESS;
    }
}

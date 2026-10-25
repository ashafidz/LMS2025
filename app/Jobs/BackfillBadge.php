<?php

namespace App\Jobs;

use App\Models\Badge;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Memberi satu badge ke semua siswa yang sudah memenuhi syaratnya (dijalankan di antrean).
 */
class BackfillBadge implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 300;

    public function __construct(public readonly int $badgeId) {}

    public function handle(): void
    {
        $badge = Badge::find($this->badgeId);

        if ($badge) {
            BadgeService::backfill($badge);
        }
    }
}

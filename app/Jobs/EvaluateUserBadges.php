<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Memeriksa badge milik satu siswa. Unik sampai mulai diproses, jadi kejadian yang datang saat job
 * berjalan tetap mengantre satu pemeriksaan baru dan tidak ada yang terlewat.
 */
class EvaluateUserBadges implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $uniqueFor = 10;

    public function __construct(public readonly int $userId) {}

    public function uniqueId(): string
    {
        return (string) $this->userId;
    }

    public function handle(): void
    {
        $user = User::find($this->userId);

        if ($user) {
            BadgeService::evaluate($user);
        }
    }
}

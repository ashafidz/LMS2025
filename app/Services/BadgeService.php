<?php

namespace App\Services;

use App\Jobs\EvaluateUserBadges;
use App\Models\Badge;
use App\Models\User;
use App\Support\Badges\BadgeMetrics;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BadgeService
{
    private const BATCH_SIZE = 500;

    /**
     * Antrekan pemeriksaan badge untuk satu siswa. Aman dipanggil berkali-kali; job-nya unik per siswa.
     */
    public static function queueFor(User|int $user): void
    {
        EvaluateUserBadges::dispatch($user instanceof User ? $user->id : $user)->afterCommit();
    }

    /**
     * Progres siswa terhadap setiap syarat sebuah badge.
     *
     * @param  bool  $withSentence  Sertakan kalimat syarat (butuh nama kursus/kategori dari database); hanya untuk tampilan.
     * @return array{met: bool, rules: array<int, array{label: string, unit: string, current: int, target: int, met: bool, sentence?: string}>}
     */
    public static function progressFor(User $user, Badge $badge, bool $withSentence = false): array
    {
        $rules = [];

        foreach ($badge->rules as $rule) {
            $current = BadgeMetrics::count($rule->metric, $user, $rule->scope, $rule->scope_id);

            $rules[] = [
                'label' => BadgeMetrics::label($rule->metric),
                'unit' => BadgeMetrics::unit($rule->metric),
                'current' => $current,
                'target' => (int) $rule->target,
                'met' => $current >= $rule->target,
            ] + ($withSentence ? ['sentence' => $rule->sentence()] : []);
        }

        return [
            'met' => $rules !== [] && collect($rules)->every(fn (array $rule) => $rule['met']),
            'rules' => $rules,
        ];
    }

    /**
     * Beri semua badge aktif yang syaratnya terpenuhi dan belum dimiliki siswa. Badge tidak pernah dicabut.
     *
     * @return Collection<int, Badge> Badge yang baru diberikan.
     */
    public static function evaluate(User $user): Collection
    {
        if (! $user->hasRole('student')) {
            return collect();
        }

        $candidates = Badge::where('is_active', true)
            ->whereHas('rules')
            ->whereDoesntHave('users', fn ($q) => $q->where('users.id', $user->id))
            ->with('rules')
            ->get();

        $awarded = collect();

        foreach ($candidates as $badge) {
            if (self::meetsAllRules($user, $badge) && self::grant($user, $badge)) {
                $awarded->push($badge);
            }
        }

        return $awarded;
    }

    /**
     * Beri badge ke semua siswa yang sudah memenuhi syaratnya. Siswa yang memenuhi dicari dengan query agregat
     * (satu query per syarat untuk semua siswa), lalu diberikan per batch.
     *
     * @return int Jumlah siswa yang baru mendapat badge.
     */
    public static function backfill(Badge $badge): int
    {
        $badge->loadMissing('rules');

        if (! $badge->is_active || $badge->rules->isEmpty()) {
            return 0;
        }

        $holders = array_flip(DB::table('badge_user')->where('badge_id', $badge->id)->pluck('user_id')->all());
        $pending = array_values(array_filter(
            self::qualifyingUserIds($badge->rules),
            fn (int $id) => ! isset($holders[$id])
        ));

        $granted = 0;

        foreach (array_chunk($pending, self::BATCH_SIZE) as $batch) {
            // insertOrIgnore: jika proses lain memberi badge yang sama bersamaan, yang kalah tidak error.
            $granted += DB::table('badge_user')->insertOrIgnore(array_map(fn (int $userId) => [
                'user_id' => $userId,
                'badge_id' => $badge->id,
                'created_at' => now(),
                'seen_at' => null,
            ], $batch));
        }

        return $granted;
    }

    /**
     * Jumlah siswa yang saat ini memenuhi semua syarat (untuk pratinjau di form admin).
     * Satu query agregat per syarat, bukan per siswa.
     *
     * @param  iterable<int, array{metric: string, target: int, scope: string, scope_id?: ?int}>  $rules
     * @return array{count: int, capped: bool}
     */
    public static function countQualifying(iterable $rules): array
    {
        return ['count' => count(self::qualifyingUserIds($rules)), 'capped' => false];
    }

    /**
     * Id siswa (role student) yang memenuhi SEMUA syarat.
     *
     * @param  iterable<int, \App\Models\BadgeRule|array{metric: string, target: int, scope: string, scope_id?: ?int}>  $rules
     * @return array<int, int>
     */
    public static function qualifyingUserIds(iterable $rules): array
    {
        $ids = null;

        foreach ($rules as $rule) {
            $rule = is_array($rule) ? $rule : $rule->only(['metric', 'target', 'scope', 'scope_id']);

            $matching = BadgeMetrics::qualifyingUserIds($rule['metric'], (int) $rule['target'], $rule['scope'], $rule['scope_id'] ?? null);
            $ids = $ids === null ? $matching : array_values(array_intersect($ids, $matching));

            if ($ids === []) {
                return [];
            }
        }

        if ($ids === null) {
            return [];
        }

        $students = array_flip(User::role('student')->pluck('users.id')->all());

        return array_values(array_filter($ids, fn (int $id) => isset($students[$id])));
    }

    /**
     * Cek ringan untuk satu siswa: berhenti di syarat pertama yang belum terpenuhi dan tidak menyusun kalimat.
     */
    private static function meetsAllRules(User $user, Badge $badge): bool
    {
        if ($badge->rules->isEmpty()) {
            return false;
        }

        foreach ($badge->rules as $rule) {
            if (BadgeMetrics::count($rule->metric, $user, $rule->scope, $rule->scope_id) < $rule->target) {
                return false;
            }
        }

        return true;
    }

    private static function grant(User $user, Badge $badge): bool
    {
        // insertOrIgnore: jika dua proses memberi badge yang sama bersamaan, yang kalah tidak error.
        return DB::table('badge_user')->insertOrIgnore([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'created_at' => now(),
            'seen_at' => null,
        ]) === 1;
    }
}

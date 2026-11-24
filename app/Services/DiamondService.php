<?php

namespace App\Services;

use App\Models\Course;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DiamondService
{
    /**
     * Mengonversi poin dari satu kursus menjadi diamond.
     *
     * @return bool Berhasil atau tidak.
     */
    public static function convertFromPoints(User $user, Course $course): bool
    {
        $settings = cache()->remember('site_settings', now()->addMinutes(60), fn () => SiteSetting::first());
        $conversionRate = $settings?->point_to_diamond_rate ?? 0;

        if ($conversionRate <= 0) {
            return false;
        }

        return DB::transaction(function () use ($user, $course, $conversionRate) {
            $lockedPivot = DB::table('course_user')
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->lockForUpdate()
                ->first();

            // Validasi: Cek apakah ada poin untuk dikonversi dan belum pernah dikonversi
            if (! $lockedPivot || $lockedPivot->points_earned <= 0 || $lockedPivot->is_converted_to_diamond) {
                return false;
            }

            $pointsToConvert = $lockedPivot->points_earned;
            $diamondsEarned = (int) floor($pointsToConvert * $conversionRate);

            if ($diamondsEarned <= 0) {
                return false;
            }

            $lockedUser = User::whereKey($user->id)->lockForUpdate()->first();
            if (! $lockedUser) {
                return false;
            }

            // 1. Tambahkan diamond ke saldo pengguna
            $lockedUser->increment('diamond_balance', $diamondsEarned);

            // 2. Buat catatan di riwayat diamond
            $lockedUser->diamondHistories()->create([
                'diamonds' => $diamondsEarned,
                'description' => "Konversi {$pointsToConvert} poin dari kursus: {$course->title}",
            ]);

            // 3. Tandai bahwa poin dari kursus ini sudah dikonversi
            DB::table('course_user')
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->update(['is_converted_to_diamond' => true]);

            $user->refresh();

            return true;
        });
    }

    /**
     * Bonus diamond for a paid course purchase (ADR-016). Called by OrderSettlementService only for orders with
     * final_amount > 0, inside its transaction. Returns the diamonds given (0 when the setting is 0).
     */
    public static function awardForPurchase(User $user, Course $course): int
    {
        $diamonds = (int) (SiteSetting::first()?->diamonds_for_purchase ?? 0);

        if ($diamonds <= 0) {
            return 0;
        }

        DB::transaction(function () use ($user, $course, $diamonds) {
            $locked = User::whereKey($user->id)->lockForUpdate()->first();
            if (! $locked) {
                return;
            }

            $locked->increment('diamond_balance', $diamonds);
            $locked->diamondHistories()->create([
                'diamonds' => $diamonds,
                // Not "Membeli kursus: ...": that text is the diamonds spent in useForPurchase
                'description' => 'Bonus pembelian kursus: '.$course->title,
            ]);
        });

        $user->refresh();

        return $diamonds;
    }

    /**
     * Menggunakan diamond untuk membeli kursus.
     *
     * @return bool Berhasil atau tidak.
     */
    public static function useForPurchase(User $user, Course $course): bool
    {
        $diamondPrice = $course->diamond_price;

        return DB::transaction(function () use ($user, $course, $diamondPrice) {
            $locked = User::whereKey($user->id)->lockForUpdate()->first();

            // Validasi: Cek apakah diamond cukup dan user belum terdaftar
            if (! $locked || $locked->diamond_balance < $diamondPrice) {
                return false;
            }

            if ($locked->enrollments()->where('courses.id', $course->id)->exists()) {
                return false;
            }

            // 1. Kurangi diamond dari saldo pengguna
            $locked->decrement('diamond_balance', $diamondPrice);

            // 2. Buat catatan di riwayat diamond (dengan nilai negatif)
            $locked->diamondHistories()->create([
                'diamonds' => -$diamondPrice,
                'description' => "Membeli kursus: {$course->title}",
            ]);

            // 3. Daftarkan siswa ke kursus
            $locked->enrollments()->syncWithoutDetaching([
                $course->id => ['enrolled_at' => now()],
            ]);

            BadgeService::queueFor($locked);

            $user->refresh();

            return true;
        });
    }
}

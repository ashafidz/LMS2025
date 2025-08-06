<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\SiteSetting;

class DiamondService
{
    /**
     * Mengonversi poin dari satu kursus menjadi diamond.
     *
     * @param User $user
     * @param Course $course
     * @return bool Berhasil atau tidak.
     */
    public static function convertFromPoints(User $user, Course $course): bool
    {
        $courseUser = $user->coursePoints()->where('course_id', $course->id)->first();

        // Validasi: Cek apakah ada poin untuk dikonversi dan belum pernah dikonversi
        if (!$courseUser || $courseUser->pivot->points_earned <= 0 || $courseUser->pivot->is_converted_to_diamond) {
            return false;
        }

        $pointsToConvert = $courseUser->pivot->points_earned;

        // LOGIKA BARU: Ambil rasio konversi dari pengaturan
        $settings = cache()->remember('site_settings', now()->addMinutes(60), fn() => SiteSetting::firstOrFail());
        $conversionRate = $settings->point_to_diamond_rate;

        // Hitung diamond yang didapat
        $diamondsEarned = floor($pointsToConvert * $conversionRate);

        // Jangan proses jika hasilnya 0 diamond
        if ($diamondsEarned <= 0) {
            return false;
        }

        DB::transaction(function () use ($user, $course, $pointsToConvert, $diamondsEarned, $courseUser) {
            // 1. Tambahkan diamond ke saldo pengguna
            $user->increment('diamond_balance', $diamondsEarned);

            // 2. Buat catatan di riwayat diamond
            $user->diamondHistories()->create([
                'diamonds' => $diamondsEarned,
                'description' => "Konversi {$pointsToConvert} poin dari kursus: {$course->title}",
            ]);

            // 3. Tandai bahwa poin dari kursus ini sudah dikonversi
            $courseUser->pivot->is_converted_to_diamond = true;
            $courseUser->pivot->save();
        });

        return true;
    }

    /**
     * Menggunakan diamond untuk membeli kursus.
     *
     * @param User $user
     * @param Course $course
     * @return bool Berhasil atau tidak.
     */
    public static function useForPurchase(User $user, Course $course): bool
    {
        $diamondPrice = $course->diamond_price;

        // Validasi: Cek apakah diamond cukup
        if ($user->diamond_balance < $diamondPrice) {
            return false;
        }

        DB::transaction(function () use ($user, $course, $diamondPrice) {
            // 1. Kurangi diamond dari saldo pengguna
            $user->decrement('diamond_balance', $diamondPrice);

            // 2. Buat catatan di riwayat diamond (dengan nilai negatif)
            $user->diamondHistories()->create([
                'diamonds' => -$diamondPrice,
                'description' => "Membeli kursus: {$course->title}",
            ]);

            // 3. Daftarkan siswa ke kursus
            $user->enrollments()->attach($course->id, ['enrolled_at' => now()]);
        });

        return true;
    }
}

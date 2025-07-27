<?php

namespace App\Services;

use App\Models\User;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;

class PointService
{
    /**
     * Menambahkan poin ke pengguna dan mencatat riwayatnya.
     *
     * @param User $user Pengguna yang akan menerima poin.
     * @param string $activity Tipe aktivitas (misal: 'purchase', 'complete_video').
     * @param string|null $description_meta Informasi tambahan untuk deskripsi (misal: nama pelajaran).
     * @return void
     */
    public static function addPoints(User $user, string $activity, $description_meta = null)
    {
        // Ambil pengaturan poin dari database (menggunakan cache untuk efisiensi)
        $settings = cache()->remember('site_settings', 60, function () {
            return SiteSetting::firstOrFail();
        });

        $pointsToAdd = 0;
        $description = '';

        switch ($activity) {
            case 'purchase':
                $pointsToAdd = $settings->points_for_purchase;
                $description = 'Membeli kursus: ' . $description_meta;
                break;
            case 'complete_article':
                $pointsToAdd = $settings->points_for_article;
                $description = 'Menyelesaikan pelajaran artikel: ' . $description_meta;
                break;
            case 'complete_video':
                $pointsToAdd = $settings->points_for_video;
                $description = 'Menyelesaikan pelajaran video: ' . $description_meta;
                break;
            case 'complete_document':
                $pointsToAdd = $settings->points_for_document;
                $description = 'Menyelesaikan pelajaran dokumen: ' . $description_meta;
                break;
            case 'pass_quiz':
                $pointsToAdd = $settings->points_for_quiz;
                $description = 'Lulus kuis: ' . $description_meta;
                break;
            case 'pass_assignment':
                $pointsToAdd = $settings->points_for_assignment;
                $description = 'Lulus tugas: ' . $description_meta;
                break;
        }

        if ($pointsToAdd > 0) {
            // Gunakan DB transaction untuk memastikan konsistensi data
            \Illuminate\Support\Facades\DB::transaction(function () use ($user, $pointsToAdd, $description) {
                // 1. Tambahkan poin ke saldo pengguna
                $user->increment('points_balance', $pointsToAdd);

                // 2. Buat catatan di riwayat poin
                $user->pointHistories()->create([
                    'points' => $pointsToAdd,
                    'description' => $description,
                ]);
            });
        }
    }

    /**
     * METODE BARU: Menggunakan (mengurangi) poin pengguna dan mencatat riwayatnya.
     *
     * @param User $user Pengguna yang akan menggunakan poin.
     * @param int $pointsToUse Jumlah poin yang akan dikurangi.
     * @param string $description Deskripsi untuk riwayat (misal: nama kursus).
     * @return void
     */
    public static function usePoints(User $user, int $pointsToUse, string $description)
    {
        // Pastikan poin yang akan digunakan tidak negatif
        if ($pointsToUse <= 0) {
            return;
        }

        // Gunakan DB transaction untuk memastikan konsistensi data
        DB::transaction(function () use ($user, $pointsToUse, $description) {
            // 1. Kurangi poin dari saldo pengguna
            $user->decrement('points_balance', $pointsToUse);

            // 2. Buat catatan di riwayat poin (dengan nilai negatif)
            $user->pointHistories()->create([
                'points' => -$pointsToUse,
                'description' => $description,
            ]);
        });
    }
}

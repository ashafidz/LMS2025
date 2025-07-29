<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;

class BadgeService
{
    /**
     * Memeriksa dan memberikan badge kelulusan kuis kepada pengguna.
     * Metode ini harus dipanggil setiap kali seorang siswa berhasil lulus kuis.
     *
     * @param User $user Pengguna yang baru saja lulus kuis.
     * @return void
     */
    public static function checkQuizCompletionBadges(User $user)
    {
        // Hitung total kuis yang sudah pernah dilulusi oleh pengguna
        $passedQuizzesCount = $user->quizAttempts()->where('status', 'passed')->count();

        // --- Aturan untuk Badge "Quiz Master" ---
        // Anda bisa membuat ini lebih dinamis dengan menyimpannya di database nanti
        $requiredCount = 10;
        $quizBadgeId = 1; // Asumsikan ID untuk badge "Lulus 10 Kuis" adalah 1

        // Cek apakah siswa sudah memenuhi syarat
        if ($passedQuizzesCount >= $requiredCount) {
            // Cek apakah siswa sudah memiliki badge ini
            $hasBadge = $user->badges()->where('badge_id', $quizBadgeId)->exists();

            if (!$hasBadge) {
                // Jika belum, berikan badge-nya
                $user->badges()->attach($quizBadgeId);

                // Di sini Anda bisa menambahkan logika untuk mengirim notifikasi
                // kepada siswa bahwa mereka mendapatkan badge baru.
            }
        }

        // Anda bisa menambahkan blok 'if' lain di sini untuk badge selanjutnya
        // Contoh: if ($passedQuizzesCount >= 25) { ... } untuk badge "Quiz Expert"
    }

    // Kita bisa menambahkan metode lain di sini nanti, contohnya:
    // public static function checkCourseCompletionBadges(User $user) { ... }
}

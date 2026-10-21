<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptQuestionOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuizShuffleService
{
    /**
     * Algoritma Fisher-Yates Shuffle
     * Menghasilkan urutan soal yang diacak untuk satu percobaan kuis (attempt)
     */
    public function generateShuffledOrder(QuizAttempt $attempt): bool
    {
        try {
            // Memulai transaksi database untuk memastikan data tersimpan dengan aman
            DB::beginTransaction();

            // Ambil data kuis yang sedang dikerjakan
            $quiz = $attempt->quiz;

            // Ambil pengaturan keamanan kuis untuk cek apakah fitur acak (shuffle) aktif
            $securitySetting = $quiz->securitySetting;
            $isShuffleEnabled = $securitySetting && $securitySetting->enable_question_shuffle;

            // Ambil semua ID soal yang terhubung dengan kuis ini dari tabel pivot 'quiz_question'
            // Menggunakan DB::table untuk menghindari masalah soft deletes jika ada
            $questions = DB::table('quiz_question')
                ->where('quiz_id', $quiz->id)
                ->whereNull('deleted_at')
                ->pluck('question_id')
                ->toArray();

            // Jika kuis tidak punya soal, catat di log dan batalkan proses
            if (empty($questions)) {
                Log::warning("Kuis {$quiz->id} tidak memiliki soal");
                DB::commit();

                return false;
            }

            // Jika fitur shuffle aktif, jalankan algoritma Fisher-Yates
            if ($isShuffleEnabled) {
                $shuffledQuestions = $this->fisherYatesShuffle($questions);
            } else {
                // Jika tidak aktif, gunakan urutan asli dari database
                $shuffledQuestions = $questions;
            }

            // Simpan setiap soal dan urutannya ke tabel 'quiz_attempt_question_order' dalam satu query
            // Ini agar urutan soal tetap sama jika siswa me-refresh halaman
            $now = now();
            $rows = [];
            foreach ($shuffledQuestions as $index => $questionId) {
                $rows[] = [
                    'attempt_id' => $attempt->id, // Hubungkan dengan percobaan kuis siswa
                    'question_id' => $questionId, // ID soal
                    'shuffled_order' => $index + 1, // Urutan (dimulai dari 1)
                    'created_at' => $now,
                ];
            }
            QuizAttemptQuestionOrder::insert($rows);

            // Selesaikan transaksi database
            DB::commit();

            return true;
        } catch (\Exception $e) {
            // Jika terjadi error, batalkan semua perubahan database
            DB::rollBack();
            Log::error('Gagal membuat urutan soal acak: '.$e->getMessage(), [
                'attempt_id' => $attempt->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Implementasi Algoritma Fisher-Yates Shuffle
     * Mengacak elemen array secara adil dan efisien
     */
    private function fisherYatesShuffle(array $array): array
    {
        $count = count($array);

        // Mulai acak dari elemen terakhir ke depan
        for ($i = $count - 1; $i > 0; $i--) {
            // Ambil angka acak (index) antara 0 sampai $i
            $j = random_int(0, $i);

            // Proses Tukar (Swap) posisi elemen $i dengan elemen acak $j
            $temp = $array[$i];
            $array[$i] = $array[$j];
            $array[$j] = $temp;
        }

        return $array;
    }

    /**
     * Mengambil daftar soal yang sudah diacak untuk satu percobaan (attempt)
     */
    public function getShuffledQuestions(QuizAttempt $attempt)
    {
        // Ambil data soal lewat tabel urutan acak dan urutkan berdasarkan kolom 'shuffled_order'
        return $attempt->questionOrders()
            ->with('question.options') // Ambil juga pilihan jawabannya
            ->orderBy('shuffled_order') // Urutkan sesuai hasil acak tadi
            ->get()
            ->pluck('question'); // Ambil objek model 'question' saja
    }
}

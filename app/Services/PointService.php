<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use App\Models\SiteSetting;
use App\Models\CourseUser;
use Illuminate\Support\Facades\DB;

class PointService
{
    /**
     * Menambahkan poin ke pengguna untuk kursus tertentu dan mencatat riwayatnya.
     *
     * @param User $user Pengguna yang akan menerima poin.
     * @param Course $course Kursus di mana poin didapatkan.
     * @param string $activity Tipe aktivitas (misal: 'complete_video').
     * @param string|null $description_meta Informasi tambahan (misal: nama pelajaran).
     * @return void
     */
    public static function addPoints(User $user, Course $course, string $activity, $description_meta = null)
    {
        // Ambil pengaturan poin dari database (menggunakan cache untuk efisiensi)
        $settings = cache()->remember('site_settings', now()->addMinutes(60), function () {
            return SiteSetting::firstOrFail();
        });

        $pointsToAdd = 0;
        $description = '';

        switch ($activity) {
            case 'purchase':
                $pointsToAdd = $settings->points_for_purchase;
                $description = 'Membeli kursus: ' . $course->title;
                break;
            case 'complete_article':
                $pointsToAdd = $settings->points_for_article;
                $description = 'Menyelesaikan artikel: ' . $description_meta;
                break;
            case 'complete_video':
                $pointsToAdd = $settings->points_for_video;
                $description = 'Menyelesaikan video: ' . $description_meta;
                break;
            case 'complete_document':
                $pointsToAdd = $settings->points_for_document;
                $description = 'Menyelesaikan dokumen: ' . $description_meta;
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
            DB::transaction(function () use ($user, $course, $pointsToAdd, $description) {
                // 1. Buat atau update total poin di tabel pivot course_user
                // $courseUser = $user->coursePoints()->where('course_id', $course->id)->first();
                // if ($courseUser) {
                //     $courseUser->increment('points_earned', $pointsToAdd);
                // } else {
                //     $user->coursePoints()->attach($course->id, ['points_earned' => $pointsToAdd]);
                // }
                CourseUser::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'course_id' => $course->id
                    ],
                    [
                        'points_earned' => DB::raw('points_earned + ' . $pointsToAdd)
                    ]
                );

                // 2. Buat catatan di riwayat poin
                $user->pointHistories()->create([
                    'course_id' => $course->id,
                    'points' => $pointsToAdd,
                    'description' => $description,
                ]);
            });
        }
    }

    /**
     * Menambahkan poin secara manual oleh instruktur untuk pelajaran tipe 'lessonpoin'.
     */
    public static function addManualPoints(User $user, Course $course, int $pointsToAdd, string $description)
    {
        if ($pointsToAdd > 0) {
            DB::transaction(function () use ($user, $course, $pointsToAdd, $description) {
                // $courseUser = $user->coursePoints()->where('course_id', $course->id)->first();
                // if ($courseUser) {
                //     $courseUser->increment('points_earned', $pointsToAdd);
                // } else {
                //     $user->coursePoints()->attach($course->id, ['points_earned' => $pointsToAdd]);
                // }
                // **CORRECTED LOGIC**: Also apply the fix here for consistency
                CourseUser::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'course_id' => $course->id
                    ],
                    [
                        'points_earned' => DB::raw('points_earned + ' . $pointsToAdd)
                    ]
                );

                $user->pointHistories()->create([
                    'course_id' => $course->id,
                    'points' => $pointsToAdd,
                    'description' => $description,
                ]);
            });
        }
    }
}

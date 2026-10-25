<?php

namespace App\Support\Badges;

use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseUser;
use App\Models\LessonAssignment;
use App\Models\PointHistory;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Katalog ukuran yang bisa dipakai admin untuk menyusun syarat badge.
 * Menambah ukuran baru = menambah satu entri di sini; admin tidak perlu menyentuh kode.
 */
class BadgeMetrics
{
    public const SCOPE_ALL = 'all';

    public const SCOPE_COURSE = 'course';

    public const SCOPE_CATEGORY = 'category';

    /**
     * Nilai kuis sempurna. revised_score adalah skor mentah dari instruktur (dibatasi skor maksimum kuis), jadi
     * dibandingkan dengan jumlah skor soal kuis itu; tanpa revisi, scaled_score (0-100) harus 100.
     */
    private const PERFECT_SCORE_SQL = '((quiz_attempts.revised_score IS NULL AND quiz_attempts.scaled_score >= 100)'
        .' OR (quiz_attempts.revised_score IS NOT NULL AND quiz_attempts.revised_score >= NULLIF(('
        .'SELECT SUM(questions.score) FROM quiz_question'
        .' INNER JOIN questions ON questions.id = quiz_question.question_id'
        .' WHERE quiz_question.quiz_id = quiz_attempts.quiz_id), 0)))';

    /** @var array<string, array{label: string, unit: string, scopes: array<int, string>}> */
    private const DEFINITIONS = [
        'quiz_passed' => ['label' => 'Lulus kuis', 'unit' => 'kuis', 'scopes' => ['all', 'course', 'category']],
        'quiz_perfect' => ['label' => 'Nilai kuis sempurna (100)', 'unit' => 'kuis', 'scopes' => ['all', 'course', 'category']],
        'lessons_completed' => ['label' => 'Menyelesaikan pelajaran', 'unit' => 'pelajaran', 'scopes' => ['all', 'course', 'category']],
        'certificates_earned' => ['label' => 'Mendapat sertifikat kursus', 'unit' => 'sertifikat', 'scopes' => ['all', 'category']],
        'assignments_passed' => ['label' => 'Lulus tugas', 'unit' => 'tugas', 'scopes' => ['all', 'course', 'category']],
        'points_total' => ['label' => 'Mengumpulkan poin', 'unit' => 'poin', 'scopes' => ['all', 'course']],
        'reviews_written' => ['label' => 'Menulis ulasan kursus', 'unit' => 'ulasan', 'scopes' => ['all', 'category']],
        'discussions_posted' => ['label' => 'Aktif berdiskusi (komentar)', 'unit' => 'komentar', 'scopes' => ['all', 'course', 'category']],
        'courses_enrolled' => ['label' => 'Mengikuti kursus', 'unit' => 'kursus', 'scopes' => ['all', 'category']],
    ];

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_keys(self::DEFINITIONS);
    }

    /**
     * @return array<string, string> key => label untuk dropdown.
     */
    public static function options(): array
    {
        return array_map(fn (array $definition) => $definition['label'], self::DEFINITIONS);
    }

    /**
     * Data lengkap untuk editor di browser (label, satuan, scope yang didukung).
     *
     * @return array<string, array{label: string, unit: string, scopes: array<int, string>}>
     */
    public static function catalog(): array
    {
        return self::DEFINITIONS;
    }

    public static function label(string $key): string
    {
        return self::DEFINITIONS[$key]['label'] ?? $key;
    }

    public static function unit(string $key): string
    {
        return self::DEFINITIONS[$key]['unit'] ?? '';
    }

    /**
     * @return array<int, string>
     */
    public static function scopesFor(string $key): array
    {
        return self::DEFINITIONS[$key]['scopes'] ?? [];
    }

    public static function count(string $key, User $user, string $scope = self::SCOPE_ALL, ?int $scopeId = null): int
    {
        $courseIds = self::courseIds($scope, $scopeId);

        // Kursus atau kategori sudah dihapus: tidak ada yang bisa dihitung.
        if ($courseIds !== null && $courseIds === []) {
            return 0;
        }

        return match ($key) {
            'quiz_passed' => self::quizzes($user, $courseIds)->distinct()->count('quiz_id'),
            // Kolom score adalah nilai mentah; lihat PERFECT_SCORE_SQL untuk revisi instruktur.
            'quiz_perfect' => self::quizzes($user, $courseIds)->whereRaw(self::PERFECT_SCORE_SQL)->distinct()->count('quiz_id'),
            'lessons_completed' => $user->completedLessons()
                ->when($courseIds !== null, fn ($q) => $q->whereHas('module', fn ($m) => $m->whereIn('course_id', $courseIds)))
                ->count(),
            'certificates_earned' => $user->certificates()
                ->when($courseIds !== null, fn ($q) => $q->whereIn('course_id', $courseIds))
                ->count(),
            'assignments_passed' => AssignmentSubmission::where('user_id', $user->id)
                ->where('status', 'passed')
                ->when($courseIds !== null, fn ($q) => $q->whereHas('assignment.lesson.module', fn ($m) => $m->whereIn('course_id', $courseIds)))
                ->count(),
            'points_total' => $scope === self::SCOPE_COURSE
                ? (int) CourseUser::where('user_id', $user->id)->where('course_id', $scopeId)->value('points_earned')
                : (int) PointHistory::where('user_id', $user->id)->where('points', '>', 0)->sum('points'),
            'reviews_written' => $user->courseReviews()
                ->when($courseIds !== null, fn ($q) => $q->whereIn('course_id', $courseIds))
                ->count(),
            'discussions_posted' => $user->lessonDiscussions()
                ->where('is_deleted', false)
                ->when($courseIds !== null, fn ($q) => $q->whereHas('lesson.module', fn ($m) => $m->whereIn('course_id', $courseIds)))
                ->count(),
            'courses_enrolled' => $user->enrollments()
                ->when($courseIds !== null, fn ($q) => $q->whereIn('courses.id', $courseIds))
                ->count(),
            default => 0,
        };
    }

    /**
     * Id semua user yang memenuhi satu syarat, dihitung dengan SATU query agregat (GROUP BY user ... HAVING)
     * untuk semua siswa sekaligus. Hasilnya harus selalu sama dengan count() per user >= target.
     * Pemanggil yang membatasi hasil ke siswa saja (role) adalah BadgeService.
     *
     * @return array<int, int>
     */
    public static function qualifyingUserIds(string $key, int $target, string $scope = self::SCOPE_ALL, ?int $scopeId = null): array
    {
        $courseIds = self::courseIds($scope, $scopeId);

        if ($courseIds !== null && $courseIds === []) {
            return [];
        }

        $ids = match ($key) {
            'quiz_passed' => self::quizAttemptRows($courseIds)
                ->groupBy('student_id')->havingRaw('COUNT(DISTINCT quiz_id) >= ?', [$target])->pluck('student_id'),
            'quiz_perfect' => self::quizAttemptRows($courseIds)
                ->whereRaw(self::PERFECT_SCORE_SQL)
                ->groupBy('student_id')->havingRaw('COUNT(DISTINCT quiz_id) >= ?', [$target])->pluck('student_id'),
            'lessons_completed' => DB::table('lesson_user')
                ->when($courseIds !== null, fn ($q) => $q->whereIn('lesson_id', self::lessonIdsIn($courseIds)))
                ->groupBy('user_id')->havingRaw('COUNT(*) >= ?', [$target])->pluck('user_id'),
            'certificates_earned' => DB::table('certificates')
                ->when($courseIds !== null, fn ($q) => $q->whereIn('course_id', $courseIds))
                ->groupBy('user_id')->havingRaw('COUNT(*) >= ?', [$target])->pluck('user_id'),
            'assignments_passed' => DB::table('assignment_submissions')
                ->where('status', 'passed')
                ->when($courseIds !== null, fn ($q) => $q->whereIn('assignment_id', self::lessonableIdsIn((new LessonAssignment)->getMorphClass(), $courseIds)))
                ->groupBy('user_id')->havingRaw('COUNT(*) >= ?', [$target])->pluck('user_id'),
            'points_total' => $scope === self::SCOPE_COURSE
                ? DB::table('course_user')->where('course_id', $scopeId)->where('points_earned', '>=', $target)->pluck('user_id')
                : DB::table('point_histories')->where('points', '>', 0)
                    ->groupBy('user_id')->havingRaw('SUM(points) >= ?', [$target])->pluck('user_id'),
            'reviews_written' => DB::table('course_reviews')
                ->when($courseIds !== null, fn ($q) => $q->whereIn('course_id', $courseIds))
                ->groupBy('user_id')->havingRaw('COUNT(*) >= ?', [$target])->pluck('user_id'),
            'discussions_posted' => DB::table('lesson_discussions')
                ->where('is_deleted', false)
                ->when($courseIds !== null, fn ($q) => $q->whereIn('lesson_id', self::lessonIdsIn($courseIds)))
                ->groupBy('user_id')->havingRaw('COUNT(*) >= ?', [$target])->pluck('user_id'),
            // join ke courses: kursus yang sudah dihapus (soft delete) tidak dihitung, sama dengan $user->enrollments()
            'courses_enrolled' => DB::table('course_enrollments')
                ->join('courses', 'courses.id', '=', 'course_enrollments.course_id')
                ->whereNull('courses.deleted_at')
                ->when($courseIds !== null, fn ($q) => $q->whereIn('course_enrollments.course_id', $courseIds))
                ->groupBy('course_enrollments.user_id')->havingRaw('COUNT(*) >= ?', [$target])->pluck('course_enrollments.user_id'),
            default => collect(),
        };

        return $ids->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Kalimat awam untuk satu syarat, contoh: "Lulus kuis minimal 10 kuis di semua kursus".
     */
    public static function sentence(string $key, int $target, string $scope = self::SCOPE_ALL, ?int $scopeId = null): string
    {
        return trim(sprintf(
            '%s minimal %s %s %s',
            self::label($key),
            number_format($target, 0, ',', '.'),
            self::unit($key),
            self::scopeText($scope, $scopeId)
        ));
    }

    public static function scopeText(string $scope, ?int $scopeId): string
    {
        return match ($scope) {
            self::SCOPE_COURSE => 'di kursus '.(Course::whereKey($scopeId)->value('title') ?? '(kursus yang sudah dihapus)'),
            self::SCOPE_CATEGORY => 'di kategori '.(CourseCategory::whereKey($scopeId)->value('name') ?? '(kategori yang sudah dihapus)'),
            default => 'di semua kursus',
        };
    }

    /**
     * Daftar id kursus untuk scope; null berarti tanpa batasan.
     *
     * @return array<int, int>|null
     */
    private static function courseIds(string $scope, ?int $scopeId): ?array
    {
        return match ($scope) {
            self::SCOPE_COURSE => Course::whereKey($scopeId)->pluck('id')->all(),
            self::SCOPE_CATEGORY => Course::where('category_id', $scopeId)->pluck('id')->all(),
            default => null,
        };
    }

    /**
     * Attempt lulus milik user. Pemanggil menghitung dengan distinct quiz_id supaya kuis yang sama
     * tidak terhitung dua kali walau lulus berkali-kali.
     */
    private static function quizzes(User $user, ?array $courseIds)
    {
        return QuizAttempt::where('student_id', $user->id)
            ->where('status', 'passed')
            ->when($courseIds !== null, fn ($q) => $q->whereHas('quiz.lesson.module', fn ($m) => $m->whereIn('course_id', $courseIds)));
    }

    private static function quizAttemptRows(?array $courseIds): Builder
    {
        return DB::table('quiz_attempts')
            ->where('status', 'passed')
            ->when($courseIds !== null, fn ($q) => $q->whereIn('quiz_id', self::lessonableIdsIn((new Quiz)->getMorphClass(), $courseIds)));
    }

    /**
     * Id isi pelajaran (kuis atau tugas) milik kursus-kursus tertentu.
     *
     * @param  array<int, int>  $courseIds
     */
    private static function lessonableIdsIn(string $morphClass, array $courseIds): Builder
    {
        return DB::table('lessons')
            ->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->where('lessons.lessonable_type', $morphClass)
            ->whereIn('modules.course_id', $courseIds)
            ->select('lessons.lessonable_id');
    }

    /**
     * Id pelajaran milik kursus-kursus tertentu.
     *
     * @param  array<int, int>  $courseIds
     */
    private static function lessonIdsIn(array $courseIds): Builder
    {
        return DB::table('lessons')
            ->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->whereIn('modules.course_id', $courseIds)
            ->select('lessons.id');
    }
}

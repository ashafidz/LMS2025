<?php

namespace Tests\Feature\Badges;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseReview;
use App\Models\CourseUser;
use App\Models\Lesson;
use App\Models\LessonDiscussion;
use App\Models\PointHistory;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\BadgeService;
use App\Support\Badges\BadgeMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsBadgeFixtures;
use Tests\TestCase;

/**
 * Query agregat (satu query untuk semua siswa) harus memberi hasil yang sama persis dengan
 * BadgeMetrics::count per siswa, karena keduanya menentukan siapa yang mendapat badge.
 */
class BadgeAggregateTest extends TestCase
{
    use BuildsBadgeFixtures;
    use RefreshDatabase;

    private Course $courseA;

    private Course $courseB;

    private Course $trashed;

    private CourseCategory $categoryA;

    /** @var array<int, User> */
    private array $students = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryA = CourseCategory::factory()->create();
        [$this->courseA, $moduleA] = $this->makeCourseWithModule(['category_id' => $this->categoryA->id]);
        [$this->courseB, $moduleB] = $this->makeCourseWithModule();
        [$this->trashed, $moduleT] = $this->makeCourseWithModule(['category_id' => $this->categoryA->id]);

        $quizA1 = $this->giveQuizMaxScore($this->makeQuizIn($moduleA), 10);
        $quizA2 = $this->giveQuizMaxScore($this->makeQuizIn($moduleA), 10);
        $quizB1 = $this->giveQuizMaxScore($this->makeQuizIn($moduleB), 10);
        $quizT1 = $this->giveQuizMaxScore($this->makeQuizIn($moduleT), 200);
        $assignA = $this->makeAssignmentIn($moduleA);
        $assignB = $this->makeAssignmentIn($moduleB);
        $lessonsA = [Lesson::factory()->create(['module_id' => $moduleA->id]), Lesson::factory()->create(['module_id' => $moduleA->id])];
        $lessonB = Lesson::factory()->create(['module_id' => $moduleB->id]);
        $lessonT = Lesson::factory()->create(['module_id' => $moduleT->id]);

        // s0: tanpa aktivitas sama sekali
        $this->students[0] = $this->makeStudent();

        // s1: kuis A1 lulus 3 kali (dihitung 1), A2 sempurna, B1 gagal, nilai direvisi
        $s = $this->students[1] = $this->makeStudent();
        foreach (range(1, 3) as $i) {
            $this->passQuiz($s, $quizA1, 80);
        }
        $this->passQuiz($s, $quizA2, 100)->update(['score' => 10, 'scaled_score' => 100]);
        QuizAttempt::factory()->failed()->create(['quiz_id' => $quizB1->id, 'student_id' => $s->id]);
        $this->passQuiz($s, $quizT1, 90)->update(['score' => 180, 'scaled_score' => 90, 'revised_score' => 200]);
        $this->enroll($s, $this->courseA);
        $this->enroll($s, $this->trashed);
        $s->completedLessons()->attach([$lessonsA[0]->id, $lessonsA[1]->id, $lessonT->id]);
        $this->submitAssignment($s, $assignA, 'passed');
        $this->submitAssignment($s, $assignB, 'revision_required');
        PointHistory::create(['user_id' => $s->id, 'course_id' => $this->courseA->id, 'points' => 40, 'description' => 'a']);
        PointHistory::create(['user_id' => $s->id, 'course_id' => $this->courseA->id, 'points' => -15, 'description' => 'koreksi']);
        CourseUser::create(['user_id' => $s->id, 'course_id' => $this->courseA->id, 'points_earned' => 25]);

        // s2: kuis B1 + A1, perfect diturunkan revisi, ulasan, sertifikat, diskusi (satu dihapus)
        $s = $this->students[2] = $this->makeStudent();
        $this->passQuiz($s, $quizB1, 100)->update(['score' => 10, 'scaled_score' => 100]);
        $this->passQuiz($s, $quizA1, 100)->update(['score' => 10, 'scaled_score' => 100, 'revised_score' => 9]);
        $this->enroll($s, $this->courseB);
        $this->enroll($s, $this->courseA);
        $s->completedLessons()->attach([$lessonB->id]);
        $this->submitAssignment($s, $assignB, 'passed');
        $this->submitAssignment($s, $assignA, 'passed');
        CourseReview::create(['course_id' => $this->courseA->id, 'user_id' => $s->id, 'rating' => 5, 'comment' => 'ok']);
        CourseReview::create(['course_id' => $this->courseB->id, 'user_id' => $s->id, 'rating' => 4, 'comment' => 'ok']);
        Certificate::create(['user_id' => $s->id, 'course_id' => $this->courseA->id, 'certificate_code' => 'C-A', 'issued_at' => now()]);
        Certificate::create(['user_id' => $s->id, 'course_id' => $this->courseB->id, 'certificate_code' => 'C-B', 'issued_at' => now()]);
        LessonDiscussion::create(['lesson_id' => $lessonB->id, 'user_id' => $s->id, 'content' => 'x']);
        LessonDiscussion::create(['lesson_id' => $lessonsA[0]->id, 'user_id' => $s->id, 'content' => 'y']);
        LessonDiscussion::create(['lesson_id' => $lessonsA[0]->id, 'user_id' => $s->id, 'content' => 'dihapus', 'is_deleted' => true]);
        PointHistory::create(['user_id' => $s->id, 'course_id' => $this->courseB->id, 'points' => 60, 'description' => 'b']);
        CourseUser::create(['user_id' => $s->id, 'course_id' => $this->courseB->id, 'points_earned' => 60]);
        CourseUser::create(['user_id' => $s->id, 'course_id' => $this->courseA->id, 'points_earned' => 5]);

        // s3: hanya ulasan dan pendaftaran di kursus B, plus revisi 150 dari 200 (75%, bukan sempurna)
        $s = $this->students[3] = $this->makeStudent();
        $this->passQuiz($s, $quizT1, 75)->update(['score' => 150, 'scaled_score' => 75, 'revised_score' => 150]);
        CourseReview::create(['course_id' => $this->courseB->id, 'user_id' => $s->id, 'rating' => 3, 'comment' => 'ok']);
        $this->enroll($s, $this->courseB);

        // Kursus dihapus (soft delete) setelah semua data terisi
        $this->trashed->delete();
    }

    private function expected(string $metric, int $target, string $scope, ?int $scopeId): array
    {
        return User::role('student')->get()
            ->filter(fn (User $user) => BadgeMetrics::count($metric, $user, $scope, $scopeId) >= $target)
            ->pluck('id')->sort()->values()->all();
    }

    private function actual(string $metric, int $target, string $scope, ?int $scopeId): array
    {
        $students = User::role('student')->pluck('id')->all();

        return collect(BadgeMetrics::qualifyingUserIds($metric, $target, $scope, $scopeId))
            ->intersect($students)->sort()->values()->all();
    }

    public function test_aggregate_matches_per_user_count_for_every_metric_scope_and_target(): void
    {
        $scopes = [
            ['all', null],
            ['course', $this->courseA->id],
            ['course', $this->courseB->id],
            ['course', $this->trashed->id],
            ['course', 999999],
            ['category', $this->categoryA->id],
            ['category', 999999],
        ];
        $checked = 0;

        foreach (BadgeMetrics::keys() as $metric) {
            foreach ($scopes as [$scope, $scopeId]) {
                if (! in_array($scope, BadgeMetrics::scopesFor($metric), true)) {
                    continue;
                }
                foreach ([1, 2, 3, 50] as $target) {
                    $this->assertSame(
                        $this->expected($metric, $target, $scope, $scopeId),
                        $this->actual($metric, $target, $scope, $scopeId),
                        "$metric / $scope".($scopeId ? "#$scopeId" : '')." / target $target"
                    );
                    $checked++;
                }
            }
        }

        $this->assertGreaterThan(60, $checked);
    }

    public function test_data_actually_exercises_distinct_and_revision_and_trashed_course_rules(): void
    {
        $ids = fn (string $metric, int $target, string $scope = 'all', ?int $scopeId = null) => $this->actual($metric, $target, $scope, $scopeId);

        // s1 lulus A1 tiga kali + A2 + T1 (kursus dihapus tidak dihitung pada cakupan kategori)
        $this->assertContains($this->students[1]->id, $ids('quiz_passed', 2, 'category', $this->categoryA->id));
        $this->assertNotContains($this->students[1]->id, $ids('quiz_passed', 3, 'category', $this->categoryA->id));
        // nilai sempurna: s1 (A2 skala 100 dan T1 revisi 100), s2 (B1 skala 100, A1 diturunkan jadi 90)
        $this->assertContains($this->students[1]->id, $ids('quiz_perfect', 2));
        $this->assertNotContains($this->students[2]->id, $ids('quiz_perfect', 2));
        $this->assertContains($this->students[2]->id, $ids('quiz_perfect', 1));
        $this->assertNotContains($this->students[3]->id, $ids('quiz_perfect', 1));
        // pendaftaran di kursus yang dihapus tidak dihitung
        $this->assertNotContains($this->students[1]->id, $ids('courses_enrolled', 2));
        $this->assertContains($this->students[2]->id, $ids('courses_enrolled', 2));
        // poin: 40 positif (koreksi negatif diabaikan) vs total kursus 25
        $this->assertContains($this->students[1]->id, $ids('points_total', 40));
        $this->assertNotContains($this->students[1]->id, $ids('points_total', 30, 'course', $this->courseA->id));
        // komentar yang dihapus tidak dihitung
        $this->assertContains($this->students[2]->id, $ids('discussions_posted', 2));
        $this->assertNotContains($this->students[2]->id, $ids('discussions_posted', 3));
    }

    public function test_count_qualifying_requires_every_rule_and_only_students(): void
    {
        $instructor = $this->makeInstructor();
        $this->passQuiz($instructor, $this->makeQuizIn(\App\Models\Module::where('course_id', $this->courseB->id)->first()));

        $rules = [
            ['metric' => 'quiz_passed', 'target' => 1, 'scope' => 'all', 'scope_id' => null],
            ['metric' => 'reviews_written', 'target' => 2, 'scope' => 'all', 'scope_id' => null],
        ];

        $result = BadgeService::countQualifying($rules);

        $this->assertSame(1, $result['count']); // hanya s2; instruktur lulus kuis tetapi bukan siswa
        $this->assertFalse($result['capped']);
    }

    public function test_count_qualifying_uses_a_fixed_number_of_queries_regardless_of_student_count(): void
    {
        $rules = [
            ['metric' => 'quiz_passed', 'target' => 1, 'scope' => 'category', 'scope_id' => $this->categoryA->id],
            ['metric' => 'lessons_completed', 'target' => 1, 'scope' => 'course', 'scope_id' => $this->courseB->id],
        ];

        $queries = function () use ($rules): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            BadgeService::countQualifying($rules);

            return count(DB::getQueryLog());
        };

        $few = $queries();
        foreach (range(1, 40) as $i) {
            $student = $this->makeStudent();
            $this->enroll($student, $this->courseB);
        }
        $many = $queries();

        $this->assertSame($few, $many, 'jumlah query tidak boleh bertambah mengikuti jumlah siswa');
        $this->assertLessThanOrEqual(10, $many);
    }

    public function test_backfill_grants_in_batches_with_a_fixed_number_of_queries_per_batch(): void
    {
        $badge = $this->makeBadge([['metric' => 'courses_enrolled', 'target' => 1]]);
        foreach (range(1, 450) as $i) {
            $student = $this->makeStudent();
            $this->enroll($student, $this->courseB);
        }
        $qualifying = BadgeService::countQualifying($badge->rules->map->only(['metric', 'target', 'scope', 'scope_id'])->all())['count'];

        DB::flushQueryLog();
        DB::enableQueryLog();
        $granted = BadgeService::backfill($badge);
        $queryCount = count(DB::getQueryLog());

        $this->assertSame($qualifying, $granted);
        $this->assertSame($qualifying, DB::table('badge_user')->where('badge_id', $badge->id)->count());
        $this->assertLessThan(30, $queryCount, "backfill $granted siswa memakai $queryCount query");
        $this->assertSame(0, BadgeService::backfill($badge), 'pemanggilan kedua tidak memberi apa pun');
    }
}

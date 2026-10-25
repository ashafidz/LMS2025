<?php

namespace Tests\Feature\Quiz;

use App\Models\PointHistory;
use App\Models\QuizAttempt;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsBadgeFixtures;
use Tests\TestCase;

/**
 * K5: instruktur bisa meluluskan percobaan yang gagal HANYA karena terlambat (mis. internet putus),
 * dengan catatan alasan, bila nilai akhirnya mencapai batas lulus.
 */
class ApproveLateAttemptTest extends TestCase
{
    use BuildsBadgeFixtures;
    use RefreshDatabase;

    private $instructor;

    private $student;

    private $quiz;

    protected function setUp(): void
    {
        parent::setUp();

        SiteSetting::create(['site_name' => 'ruangstudi.id', 'points_for_quiz' => 20]);
        cache()->forget('site_settings');

        [$course, $module] = $this->makeCourseWithModule();
        $this->instructor = $course->instructor;
        $this->student = $this->makeStudent();
        $this->enroll($this->student, $course);
        $this->quiz = $this->giveQuizMaxScore($this->makeQuizIn($module), 10);
        $this->quiz->update(['pass_mark' => 70, 'time_limit' => 10, 'allow_exceed_time_limit' => false]);
    }

    /** Percobaan yang dikirim 30 menit setelah mulai pada kuis 10 menit. */
    private function lateAttempt(float $raw = 9, float $scaled = 90): QuizAttempt
    {
        return QuizAttempt::create([
            'quiz_id' => $this->quiz->id,
            'student_id' => $this->student->id,
            'score' => $raw,
            'scaled_score' => $scaled,
            'status' => 'failed',
            'start_time' => now()->subMinutes(30),
            'end_time' => now(),
        ]);
    }

    private function approve(QuizAttempt $attempt, array $data = ['late_override_note' => 'Internet sekolah putus saat ujian'], $as = null)
    {
        return $this->actingAs($as ?? $this->instructor)->withSession(['active_role' => 'instructor'])
            ->post(route('instructor.quiz.attempt.approve_late', $attempt), $data);
    }

    public function test_instructor_approves_a_late_attempt_like_a_normal_pass(): void
    {
        $badge = $this->makeBadge([['metric' => 'quiz_passed', 'target' => 1]]);
        $attempt = $this->lateAttempt();
        $this->assertTrue($attempt->failedBecauseLate());

        $this->approve($attempt)->assertRedirect()->assertSessionHas('success');

        $attempt->refresh();
        $this->assertSame('passed', $attempt->status);
        $this->assertSame($this->instructor->id, $attempt->late_override_by);
        $this->assertNotNull($attempt->late_override_at);
        $this->assertSame('Internet sekolah putus saat ujian', $attempt->late_override_note);
        $this->assertDatabaseHas('lesson_user', ['user_id' => $this->student->id, 'lesson_id' => $this->quiz->lesson->id]);
        $this->assertSame(1, PointHistory::where('user_id', $this->student->id)->where('lesson_id', $this->quiz->lesson->id)->count());
        $this->assertDatabaseHas('badge_user', ['user_id' => $this->student->id, 'badge_id' => $badge->id]);
    }

    public function test_reason_is_required(): void
    {
        $attempt = $this->lateAttempt();

        $this->approve($attempt, ['late_override_note' => ''])->assertSessionHasErrors('late_override_note');

        $this->assertSame('failed', $attempt->fresh()->status);
    }

    public function test_only_the_course_instructor_can_approve(): void
    {
        $attempt = $this->lateAttempt();

        $this->approve($attempt, as: $this->makeInstructor())->assertForbidden();
        $this->actingAs($this->student)->post(route('instructor.quiz.attempt.approve_late', $attempt), ['late_override_note' => 'x'])->assertForbidden();

        $this->assertSame('failed', $attempt->fresh()->status);
    }

    public function test_attempt_that_failed_on_score_cannot_be_approved(): void
    {
        $attempt = $this->lateAttempt();
        $attempt->update(['end_time' => $attempt->start_time->copy()->addMinutes(5), 'score' => 3, 'scaled_score' => 30]);

        $this->approve($attempt)->assertSessionHas('error');

        $this->assertSame('failed', $attempt->fresh()->status);
    }

    public function test_late_attempt_below_pass_mark_needs_a_score_revision_first(): void
    {
        $attempt = $this->lateAttempt(5, 50);

        $this->approve($attempt)->assertSessionHas('error');
        $this->assertSame('failed', $attempt->fresh()->status);

        // revised_score adalah skor mentah: 8 dari 10 = 80, di atas batas lulus 70
        $attempt->update(['revised_score' => 8]);
        $this->approve($attempt)->assertSessionHas('success');
        $this->assertSame('passed', $attempt->fresh()->status);
    }

    public function test_quiz_that_allows_late_answers_is_not_eligible(): void
    {
        $this->quiz->update(['allow_exceed_time_limit' => true]);
        $attempt = $this->lateAttempt();

        $this->approve($attempt)->assertSessionHas('error');
        $this->assertSame('failed', $attempt->fresh()->status);
    }

    public function test_points_are_not_given_twice_and_second_approval_is_rejected(): void
    {
        PointHistory::create(['user_id' => $this->student->id, 'course_id' => $this->quiz->lesson->module->course_id, 'lesson_id' => $this->quiz->lesson->id, 'points' => 20, 'description' => 'sebelumnya']);
        $attempt = $this->lateAttempt();

        $this->approve($attempt)->assertSessionHas('success');
        $this->approve($attempt)->assertSessionHas('error');

        $this->assertSame(1, PointHistory::where('user_id', $this->student->id)->where('lesson_id', $this->quiz->lesson->id)->count());
    }

    public function test_monitoring_page_offers_the_action_and_then_shows_who_approved(): void
    {
        $attempt = $this->lateAttempt();
        $page = fn () => $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.quiz.monitoring.detail', $attempt))->assertOk();

        $page()->assertSee('Gagal karena terlambat')->assertSee('Luluskan percobaan ini');

        $this->approve($attempt);

        $page()->assertSee('Diluluskan oleh '.$this->instructor->name)
            ->assertSee('Internet sekolah putus saat ujian')
            ->assertDontSee('Luluskan percobaan ini');
    }

    public function test_monitoring_page_explains_when_score_is_below_pass_mark(): void
    {
        $attempt = $this->lateAttempt(5, 50);

        $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.quiz.monitoring.detail', $attempt))->assertOk()
            ->assertSee('Nilai 50 di bawah batas lulus 70')
            ->assertDontSee('Luluskan percobaan ini');
    }

    public function test_final_scaled_score_converts_raw_revision_against_quiz_max(): void
    {
        $attempt = $this->lateAttempt(5, 50);
        $this->assertEquals(50.0, $attempt->finalScaledScore());

        $attempt->update(['revised_score' => 8]);
        $this->assertEquals(80.0, $attempt->fresh()->finalScaledScore());
    }

    public function test_rejection_message_is_visible_on_the_monitoring_page(): void
    {
        $attempt = $this->lateAttempt(5, 50);
        $url = route('instructor.quiz.monitoring.detail', $attempt);

        $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])
            ->from($url)->post(route('instructor.quiz.attempt.approve_late', $attempt), ['late_override_note' => 'coba'])
            ->assertRedirect($url);

        $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])->get($url)
            ->assertSee('Percobaan ini tidak bisa diluluskan.');
    }

    /** Mulai dan kirim kuis lewat alur siswa sungguhan, $minutesLater setelah mulai. */
    private function takeQuiz(int $minutesLater, bool $correct = true): QuizAttempt
    {
        $question = $this->quiz->questions()->first();
        $right = \App\Models\QuestionOption::create(['question_id' => $question->id, 'option_text' => 'benar', 'is_correct' => true]);
        $wrong = \App\Models\QuestionOption::create(['question_id' => $question->id, 'option_text' => 'salah', 'is_correct' => false]);

        $this->actingAs($this->student)->post(route('student.quiz.begin', $this->quiz));
        $attempt = QuizAttempt::where('student_id', $this->student->id)->latest('id')->firstOrFail();
        $this->travel($minutesLater)->minutes();
        $this->actingAs($this->student)->post(route('student.quiz.submit', $attempt), ['answers' => [$question->id => ($correct ? $right : $wrong)->id]]);

        return $attempt->fresh();
    }

    public function test_t1_approval_takes_the_same_student_lock_as_quiz_submission(): void
    {
        $attempt = $this->lateAttempt();
        \DB::flushQueryLog();
        \DB::enableQueryLog();

        $this->approve($attempt)->assertSessionHas('success');

        // StudentWriteLock mengunci baris users milik siswa (di MySQL dengan FOR UPDATE); pengiriman kuis memakai kunci yang sama
        $studentLock = collect(\DB::getQueryLog())->contains(
            fn ($q) => str_starts_with($q['query'], 'select "id" from "users" where "users"."id" = ?') && $q['bindings'] === [$this->student->id]
        );
        $this->assertTrue($studentLock, 'approveLate harus memakai StudentWriteLock untuk siswa');
    }

    public function test_t2_message_does_not_claim_points_when_quiz_points_are_zero(): void
    {
        SiteSetting::query()->update(['points_for_quiz' => 0]);
        cache()->forget('site_settings');
        $attempt = $this->lateAttempt();

        $this->approve($attempt)->assertSessionHas('success', 'Percobaan diluluskan. Pelajaran ditandai selesai.');

        $this->assertSame(0, PointHistory::where('user_id', $this->student->id)->count());
    }

    public function test_t2_message_mentions_points_only_when_they_were_recorded(): void
    {
        $this->approve($this->lateAttempt())
            ->assertSessionHas('success', 'Percobaan diluluskan. Pelajaran ditandai selesai dan poin kuis diberikan ke siswa.');
    }

    public function test_t3_late_status_is_recorded_at_submission_and_survives_later_time_limit_changes(): void
    {
        $late = $this->takeQuiz(30);
        $this->assertSame('failed', $late->status);
        $this->assertTrue($late->failed_by_deadline);

        // Instruktur memperpanjang batas waktu setelah ujian: percobaan tetap tercatat gagal karena terlambat
        $this->quiz->update(['time_limit' => 60]);
        $this->assertTrue($late->fresh()->failedBecauseLate());
        $this->approve($late->fresh())->assertSessionHas('success');
    }

    public function test_t3_on_time_failure_stays_a_score_failure_after_time_limit_is_shortened(): void
    {
        $attempt = $this->takeQuiz(5, correct: false);
        $this->assertSame('failed', $attempt->status);
        $this->assertFalse($attempt->failed_by_deadline);

        $this->quiz->update(['time_limit' => 1]);
        $this->assertFalse($attempt->fresh()->failedBecauseLate());
    }

    public function test_t3_legacy_attempts_without_the_flag_fall_back_to_the_time_check(): void
    {
        $attempt = $this->lateAttempt();
        $this->assertNull($attempt->failed_by_deadline);
        $this->assertTrue($attempt->failedBecauseLate());
    }

    public function test_t4_card_warns_when_the_attempt_was_also_expelled_for_violations(): void
    {
        $attempt = $this->lateAttempt();
        $attempt->update(['expelled_by_violation' => true]);

        $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.quiz.monitoring.detail', $attempt))->assertOk()
            ->assertSee('Percobaan ini juga ditandai dikeluarkan karena pelanggaran')
            ->assertSee('Luluskan percobaan ini');
    }

    public function test_t5_override_column_has_no_foreign_key_and_survives_instructor_deletion(): void
    {
        $foreignKeys = collect(\Schema::getForeignKeys('quiz_attempts'))->flatMap(fn ($fk) => $fk['columns']);
        $this->assertNotContains('late_override_by', $foreignKeys->all());
        $this->assertContains('late_override_by', collect(\Schema::getIndexes('quiz_attempts'))->flatMap(fn ($i) => $i['columns'])->all());

        $attempt = $this->lateAttempt();
        $this->approve($attempt);
        $other = $this->makeInstructor();
        $attempt->update(['late_override_by' => $other->id]);
        $other->delete();

        $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.quiz.monitoring.detail', $attempt))->assertOk()
            ->assertSee('Diluluskan oleh instruktur');
    }
}

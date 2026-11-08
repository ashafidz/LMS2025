<?php

namespace Tests\Feature\Instructor;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class QuizResultsTableTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private $instructor;

    private $course;

    private Quiz $quiz;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instructor = $this->makeInstructor();
        $this->course = $this->makeCourse($this->instructor);
        $module = $this->makeModule($this->course);
        $this->quiz = Quiz::factory()->create(['pass_mark' => 70]);
        Lesson::factory()->create([
            'module_id' => $module->id,
            'lessonable_type' => Quiz::class,
            'lessonable_id' => $this->quiz->id,
        ]);

        // One question worth 10 points: the quiz maximum is 10.
        $topic = QuestionTopic::create(['instructor_id' => $this->instructor->id, 'name' => 'T', 'available_for_all_courses' => true]);
        $question = Question::create(['topic_id' => $topic->id, 'question_text' => '1 + 1 = ?', 'score' => 10, 'question_type' => 'multiple_choice_single']);
        $this->quiz->questions()->attach($question->id, ['order' => 1]);
    }

    private function enrolledStudent(string $name, string $nim)
    {
        $student = $this->makeStudent(['name' => $name]);
        $student->studentProfile()->update(['unique_id_number' => $nim]);
        $this->enroll($student, $this->course);

        return $student;
    }

    private function attempt($student, array $attributes): QuizAttempt
    {
        return QuizAttempt::factory()->create(array_merge(['quiz_id' => $this->quiz->id, 'student_id' => $student->id], $attributes));
    }

    private function page()
    {
        return $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.quiz.results', $this->quiz))
            ->assertOk();
    }

    /** The Nilai cell of the row that contains $name, as plain text. */
    private function nilaiCell(string $html, string $name): string
    {
        $this->assertSame(1, preg_match('#<tr>\s*<td class="text-center">\d+</td>.*?'.preg_quote($name, '#').'.*?</td>\s*<td class="text-center">(.*?)</td>#s', $html, $m), "Row for $name not found");

        return trim(preg_replace('/\s+/', ' ', strip_tags($m[1])));
    }

    public function test_nilai_is_the_best_finished_attempt(): void
    {
        $student = $this->enrolledStudent('Budi Uji', '2126600001');
        $this->attempt($student, ['score' => 6, 'scaled_score' => 60, 'status' => 'failed', 'end_time' => now()]);
        $this->attempt($student, ['score' => 8.5, 'scaled_score' => 85, 'status' => 'passed', 'end_time' => now()]);

        $this->assertSame('85', $this->nilaiCell($this->page()->getContent(), 'Budi Uji'));
    }

    public function test_nilai_uses_the_revised_score_scaled_against_the_quiz_maximum(): void
    {
        $student = $this->enrolledStudent('Ani Uji', '2126600002');
        $this->attempt($student, ['score' => 5, 'scaled_score' => 50, 'revised_score' => 9, 'status' => 'passed', 'end_time' => now()]);

        $this->assertSame('90', $this->nilaiCell($this->page()->getContent(), 'Ani Uji'));
    }

    public function test_student_without_attempts_shows_a_dash_not_zero(): void
    {
        $this->enrolledStudent('Riyan Uji', '2126600008');

        $response = $this->page()->assertSee('Belum Mengerjakan');
        $this->assertSame('-', $this->nilaiCell($response->getContent(), 'Riyan Uji'));
    }

    public function test_student_with_only_an_unfinished_attempt_is_in_progress_not_failed(): void
    {
        $student = $this->enrolledStudent('Sari Uji', '2126600009');
        $this->attempt($student, ['status' => 'in_progress']);

        $response = $this->page()->assertSee('label label-warning">Sedang Mengerjakan</label>', false);

        // The student table must not call this student "Gagal" (the history modal is out of scope and still does).
        $table = substr($response->getContent(), 0, strpos($response->getContent(), 'historyModal-'.$student->id.'" tabindex'));
        $this->assertStringNotContainsString('label label-danger">Gagal</label>', $table);
        $this->assertSame('-', $this->nilaiCell($response->getContent(), 'Sari Uji'));
    }

    public function test_failed_student_is_still_failed(): void
    {
        $student = $this->enrolledStudent('Dewi Uji', '2126600010');
        $this->attempt($student, ['score' => 4, 'scaled_score' => 40, 'status' => 'failed', 'end_time' => now()]);

        $this->page()->assertSee('label label-danger">Gagal</label>', false);
    }

    public function test_pass_mark_and_maximum_are_shown_once_above_the_table(): void
    {
        $this->enrolledStudent('Budi Uji', '2126600001');

        $response = $this->page()
            ->assertSee('Nilai minimum lulus: <strong>70</strong>', false)
            ->assertSee('Nilai maksimal: <strong>100</strong>', false);

        $this->assertSame(1, substr_count($response->getContent(), 'Nilai maksimal:'));
    }

    public function test_rows_are_numbered_in_order(): void
    {
        $this->enrolledStudent('Pertama', '2126600001');
        $this->enrolledStudent('Kedua', '2126600002');
        $this->enrolledStudent('Ketiga', '2126600003');

        $html = $this->page()->assertSee('<th class="text-center">No</th>', false)->getContent();

        preg_match_all('#<tr>\s*<td class="text-center">(\d+)</td>#', $html, $numbers);
        $this->assertSame(['1', '2', '3'], $numbers[1]);
        $this->assertLessThan(strpos($html, 'Kedua'), strpos($html, 'Pertama'));
        $this->assertLessThan(strpos($html, 'Ketiga'), strpos($html, 'Kedua'));
    }

    public function test_empty_state_spans_all_seven_columns(): void
    {
        $this->page()->assertSee('colspan="7"', false)->assertSee('Belum ada siswa yang terdaftar');
    }

    public function test_search_filters_by_name_or_nim(): void
    {
        $this->enrolledStudent('Budi Santoso', '2126600001');
        $this->enrolledStudent('Ani Lestari', '2126600002');

        $byName = $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.quiz.results', [$this->quiz, 'search' => 'budi']))->assertOk();
        $byName->assertSee('Budi Santoso')->assertDontSee('Ani Lestari')->assertSee('value="budi"', false);

        $byNim = $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.quiz.results', [$this->quiz, 'search' => '600002']))->assertOk();
        $byNim->assertSee('Ani Lestari')->assertDontSee('Budi Santoso');

        $none = $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.quiz.results', [$this->quiz, 'search' => 'zzz']))->assertOk();
        $none->assertSee('Tidak ada siswa yang cocok dengan "zzz"', false);
    }

    public function test_time_columns_show_the_best_attempt_start_end_and_minutes(): void
    {
        $this->instructor->forceFill(['timezone' => 'Asia/Jakarta'])->save();
        $student = $this->enrolledStudent('Budi Santoso', '2126600001');
        // Lower attempt first, then the best one: the time columns must follow the best attempt.
        $this->attempt($student, ['score' => 6, 'scaled_score' => 60, 'status' => 'failed',
            'start_time' => '2026-11-01 01:00:00', 'end_time' => '2026-11-01 01:10:00']);
        $this->attempt($student, ['score' => 9, 'scaled_score' => 90, 'status' => 'passed',
            'start_time' => '2026-11-02 02:00:00', 'end_time' => '2026-11-02 02:25:30']);

        $this->page()
            ->assertSee('2 Nov 2026')
            ->assertSee('09.00 - 09.25')   // 02:00-02:25 UTC shown in Asia/Jakarta (UTC+7)
            ->assertSee('25 menit')   // 25 min 30 s, whole minutes
            ->assertDontSee('08.00 - 08.10');   // the lower attempt's times are not in the table (the modal still lists it)
    }

    public function test_unfinished_student_has_no_times(): void
    {
        $student = $this->enrolledStudent('Sari Wulandari', '2126600004');
        $this->attempt($student, ['score' => null, 'scaled_score' => null, 'status' => 'in_progress',
            'start_time' => now(), 'end_time' => null]);

        $this->page()->assertSee('Sedang Mengerjakan')->assertDontSee('menit');
    }

    public function test_another_instructor_gets_403(): void
    {
        $other = $this->makeInstructor();

        $this->actingAs($other)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.quiz.results', $this->quiz))
            ->assertForbidden();
    }

    public function test_final_scaled_score_with_a_given_maximum_matches_the_default(): void
    {
        $student = $this->enrolledStudent('Budi Uji', '2126600001');
        $attempt = $this->attempt($student, ['score' => 7, 'revised_score' => 9, 'status' => 'passed', 'end_time' => now()]);
        $plain = $this->attempt($student, ['score' => 7, 'status' => 'passed', 'end_time' => now()]);

        $this->assertSame(90.0, $attempt->finalScaledScore());
        $this->assertSame($attempt->finalScaledScore(), $attempt->finalScaledScore(10.0));
        $this->assertSame($plain->finalScaledScore(), $plain->finalScaledScore(10.0));
    }

    private function sortedNames(array $query): array
    {
        $html = $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.quiz.results', array_merge([$this->quiz], $query)))
            ->assertOk()->getContent();
        $order = [];
        foreach (['Budi', 'Ani', 'Riyan'] as $name) {
            $order[$name] = strpos($html, 'Siswa'.$name.'Uji');
        }
        asort($order);

        return array_keys($order);
    }

    public function test_sort_by_nilai_both_directions_keeps_empty_scores_last(): void
    {
        $budi = $this->enrolledStudent('SiswaBudiUji', '2126600001');
        $ani = $this->enrolledStudent('SiswaAniUji', '2126600002');
        $this->enrolledStudent('SiswaRiyanUji', '2126600003'); // no attempt
        $this->attempt($budi, ['score' => 6, 'scaled_score' => 60, 'status' => 'failed']);
        $this->attempt($ani, ['score' => 9, 'scaled_score' => 90, 'status' => 'passed']);

        $this->assertSame(['Ani', 'Budi', 'Riyan'], $this->sortedNames(['sort' => 'nilai', 'dir' => 'desc']));
        $this->assertSame(['Budi', 'Ani', 'Riyan'], $this->sortedNames(['sort' => 'nilai', 'dir' => 'asc']));
    }

    public function test_sort_by_nim_descending_and_invalid_sort_falls_back_to_nim(): void
    {
        $this->enrolledStudent('SiswaBudiUji', '2126600001');
        $this->enrolledStudent('SiswaAniUji', '2126600002');
        $this->enrolledStudent('SiswaRiyanUji', '2126600003');

        $this->assertSame(['Riyan', 'Ani', 'Budi'], $this->sortedNames(['sort' => 'nim', 'dir' => 'desc']));
        $this->assertSame(['Budi', 'Ani', 'Riyan'], $this->sortedNames(['sort' => 'drop table', 'dir' => 'sideways']));
    }

    public function test_sort_header_links_toggle_direction_and_keep_the_search(): void
    {
        $this->enrolledStudent('SiswaBudiUji', '2126600001');

        $html = $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.quiz.results', [$this->quiz, 'search' => 'bud', 'sort' => 'nilai', 'dir' => 'desc']))
            ->assertOk()->getContent();

        $this->assertStringContainsString('aria-sort="descending"', $html);
        $this->assertMatchesRegularExpression('#href="[^"]*search=bud[^"]*sort=nilai[^"]*dir=asc#', str_replace('&amp;', '&', $html));
        $this->assertStringContainsString('name="sort" value="nilai"', $html);
    }

    public function test_history_modal_uses_the_revised_score_and_labels_unfinished_attempts(): void
    {
        $student = $this->enrolledStudent('SiswaModalUji', '2126600009');
        $this->attempt($student, ['score' => 6, 'scaled_score' => 60, 'revised_score' => 8, 'status' => 'passed',
            'start_time' => now()->subHour(), 'end_time' => now()->subMinutes(30)]);
        $this->attempt($student, ['score' => null, 'scaled_score' => null, 'status' => 'in_progress',
            'start_time' => now()->subMinutes(5), 'end_time' => null]);

        $html = $this->page()->getContent();
        preg_match('#<div class="modal fade" id="historyModal-'.$student->id.'".*?</table>#s', $html, $m);
        $modal = preg_replace('/\s+/', ' ', $m[0] ?? '');

        $this->assertStringContainsString('Direvisi (awal 6)', $modal);
        $this->assertStringContainsString('<strong>80</strong>', $modal);       // 8 of 10, revised
        $this->assertStringNotContainsString('<strong>60</strong>', $modal);    // not the old scaled_score
        $this->assertStringContainsString('Sedang Mengerjakan', $modal);
        $this->assertStringContainsString('Sedang dikerjakan', $modal);
        $this->assertSame(1, substr_count($modal, 'label-success'));
        $this->assertStringNotContainsString('label-danger', $modal);
    }
}

<?php

namespace Tests\Feature\Instructor;

use App\Models\AssignmentSubmission;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class SubmissionTableTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private $instructor;

    private LessonAssignment $assignment;

    private $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instructor = $this->makeInstructor();
        $this->course = $this->makeCourse($this->instructor);
        $module = $this->makeModule($this->course);
        $this->assignment = LessonAssignment::create(['instructions' => 'Kerjakan', 'due_date' => now()->addWeek(), 'pass_mark' => 70]);
        Lesson::factory()->create([
            'module_id' => $module->id,
            'lessonable_type' => LessonAssignment::class,
            'lessonable_id' => $this->assignment->id,
        ]);
    }

    private function submitted(string $name, string $nim, string $status = 'submitted'): void
    {
        $student = $this->makeStudent(['name' => $name]);
        $student->studentProfile()->update(['unique_id_number' => $nim]);
        $this->enroll($student, $this->course);

        AssignmentSubmission::create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/x.pdf',
            'submitted_at' => now(),
            'status' => $status,
        ]);
    }

    private function page()
    {
        return $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.assignment.submissions', $this->assignment))
            ->assertOk();
    }

    public function test_quick_revision_button_is_gone_but_bulk_and_grading_remain(): void
    {
        $this->submitted('Budi Uji', '2126600001');
        $this->submitted('Ani Uji', '2126600002');

        $this->page()
            ->assertDontSee('Revisi Cepat')
            ->assertSee('Minta Revisi Terpilih')
            ->assertSee('Lihat & Nilai', false);
    }

    public function test_submission_rows_are_numbered(): void
    {
        $this->submitted('Budi Uji', '2126600001');
        $this->submitted('Ani Uji', '2126600002');

        $html = $this->page()->assertSee('<th class="text-center">No</th>', false)->getContent();

        // Tab "Perlu Dinilai" (with checkboxes): No cell follows the checkbox cell.
        preg_match_all('#<td class="text-center">\s*<input type="checkbox" class="submission-checkbox[^>]*>\s*</td>\s*<td class="text-center">(\d+)</td>#', $html, $numbers);
        $this->assertSame(['1', '2'], $numbers[1]);
    }

    public function test_not_submitted_tab_has_numbers_and_a_full_width_empty_state(): void
    {
        $student = $this->makeStudent(['name' => 'Belum Uji']);
        $student->studentProfile()->update(['unique_id_number' => '2126600003']);
        $this->enroll($student, $this->course);

        $this->page()->assertSee('<td class="text-center">1</td>', false)->assertSee('Belum Uji');

        $empty = $this->makeInstructor();
        $course = $this->makeCourse($empty);
        $module = $this->makeModule($course);
        $assignment = LessonAssignment::create(['instructions' => 'X', 'due_date' => now()->addWeek(), 'pass_mark' => 70]);
        Lesson::factory()->create(['module_id' => $module->id, 'lessonable_type' => LessonAssignment::class, 'lessonable_id' => $assignment->id]);

        $this->actingAs($empty)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.assignment.submissions', $assignment))
            ->assertOk()
            ->assertSee('<td colspan="4" class="text-center">Semua siswa sudah mengumpulkan tugas.</td>', false);
    }

    public function test_search_filters_submissions_by_name_or_nim(): void
    {
        $this->submitted('Budi Santoso', '2126600001');
        $this->submitted('Ani Lestari', '2126600002', 'passed');

        $response = $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.assignment.submissions', [$this->assignment, 'search' => 'ani']))
            ->assertOk();

        $response->assertSee('Ani Lestari')->assertDontSee('Budi Santoso')->assertSee('value="ani"', false);
        $response->assertSee('Tidak ada siswa yang cocok dengan pencarian di kategori ini.');

        $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.assignment.submissions', [$this->assignment, 'search' => '600001']))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertDontSee('Ani Lestari');
    }

    public function test_sort_by_submission_time_descending_and_reopens_the_tab(): void
    {
        $this->submitted('Budi Santoso', '2126600001');
        $this->submitted('Ani Lestari', '2126600002');
        AssignmentSubmission::whereHas('user', fn ($q) => $q->where('name', 'Budi Santoso'))->update(['submitted_at' => now()->subDay()]);

        $html = $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.assignment.submissions', [$this->assignment, 'sort' => 'waktu', 'dir' => 'desc', 'tab' => 'submitted']))
            ->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'Budi Santoso'), strpos($html, 'Ani Lestari'));
        $this->assertMatchesRegularExpression('#class="tab-pane active" id="submitted"#', $html);
    }

    public function test_tab_query_opens_that_tab(): void
    {
        $this->submitted('Ani Lestari', '2126600002', 'passed');

        $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor'])
            ->get(route('instructor.assignment.submissions', [$this->assignment, 'tab' => 'passed']))
            ->assertOk()
            ->assertSee('class="tab-pane active" id="passed"', false)
            ->assertSee('class="tab-pane " id="submitted"', false);
    }
}

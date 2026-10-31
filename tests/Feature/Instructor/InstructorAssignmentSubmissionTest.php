<?php

namespace Tests\Feature\Instructor;

use App\Models\AssignmentSubmission;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class InstructorAssignmentSubmissionTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private $owner;

    private $student;

    private $course;

    private $module;

    private $assignment;

    private $lesson;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->owner = $this->makeInstructor();
        $this->student = $this->makeStudent();
        $this->course = $this->makeCourse($this->owner);
        $this->module = $this->makeModule($this->course);
        $this->enroll($this->student, $this->course);

        $this->assignment = LessonAssignment::create(['instructions' => 'Kumpulkan tugas', 'pass_mark' => 70]);
        $this->lesson = Lesson::factory()->create([
            'module_id' => $this->module->id,
            'lessonable_type' => LessonAssignment::class,
            'lessonable_id' => $this->assignment->id,
        ]);
    }

    public function test_instructor_can_view_submissions_page_with_lazy_loaded_iframes(): void
    {
        $path = 'assignment_submissions/'.$this->assignment->id.'/'.$this->student->id.'/tugas.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 tugas');

        $submission = AssignmentSubmission::create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->student->id,
            'file_path' => $path,
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('instructor.assignment.submissions', $this->assignment));

        $response->assertOk();
        $fileUrl = route('files.submission', $submission);

        // Verification: iframe uses data-src for lazy loading and src is about:blank
        $response->assertSee('data-src="'.$fileUrl.'"', false);
        $response->assertSee('src="about:blank"', false);
        $response->assertDontSee('<iframe src="'.$fileUrl.'"', false);
        $response->assertSee('rs-doc-viewer', false);

        // Verification: download link includes download=1 parameter
        $downloadUrl = route('files.submission', ['submission' => $submission, 'download' => 1]);
        $response->assertSee($downloadUrl, false);
    }

    public function test_other_instructor_cannot_view_submissions_page(): void
    {
        $otherInstructor = $this->makeInstructor();

        $response = $this->actingAs($otherInstructor)
            ->get(route('instructor.assignment.submissions', $this->assignment));

        $response->assertForbidden();
    }

    public function test_submission_modal_title_includes_student_unique_id_number(): void
    {
        $this->student->studentProfile()->update(['unique_id_number' => '5025201001']);

        $path = 'assignment_submissions/'.$this->assignment->id.'/'.$this->student->id.'/tugas.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 tugas');

        AssignmentSubmission::create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->student->id,
            'file_path' => $path,
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('instructor.assignment.submissions', $this->assignment));

        $response->assertOk();
        $response->assertSee('Detail Pengumpulan: 5025201001 - '.$this->student->name, false);
    }
}

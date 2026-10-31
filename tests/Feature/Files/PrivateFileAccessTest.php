<?php

namespace Tests\Feature\Files;

use App\Models\AssignmentSubmission;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use App\Models\LessonDocument;
use App\Models\MonitoringLog;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class PrivateFileAccessTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private $owner;

    private $student;

    private $course;

    private $module;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $this->owner = $this->makeInstructor();
        $this->student = $this->makeStudent();
        $this->course = $this->makeCourse($this->owner);
        $this->module = $this->makeModule($this->course);
        $this->enroll($this->student, $this->course);
    }

    private function documentLesson(string $disk = 'local'): Lesson
    {
        Storage::disk($disk)->put('lesson_documents/materi.pdf', '%PDF-1.4 materi');
        $document = LessonDocument::create(['file_path' => 'lesson_documents/materi.pdf']);

        return Lesson::factory()->create([
            'module_id' => $this->module->id,
            'lessonable_type' => LessonDocument::class,
            'lessonable_id' => $document->id,
        ]);
    }

    private function submission(string $disk = 'local'): AssignmentSubmission
    {
        $assignment = LessonAssignment::create(['instructions' => 'Kumpulkan', 'pass_mark' => 70]);
        Lesson::factory()->create([
            'module_id' => $this->module->id,
            'lessonable_type' => LessonAssignment::class,
            'lessonable_id' => $assignment->id,
        ]);
        $path = 'assignment_submissions/'.$assignment->id.'/'.$this->student->id.'/tugas.pdf';
        Storage::disk($disk)->put($path, '%PDF-1.4 tugas');

        return AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $this->student->id,
            'file_path' => $path,
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);
    }

    private function monitoringLog(string $disk = 'local'): MonitoringLog
    {
        $quiz = Quiz::factory()->create();
        Lesson::factory()->create([
            'module_id' => $this->module->id,
            'lessonable_type' => Quiz::class,
            'lessonable_id' => $quiz->id,
        ]);
        $attempt = QuizAttempt::factory()->create(['quiz_id' => $quiz->id, 'student_id' => $this->student->id]);
        Storage::disk($disk)->put('screenshots/violations/'.$attempt->id.'/bukti.jpg', 'jpegdata');

        return MonitoringLog::create([
            'attempt_id' => $attempt->id,
            'violation_type' => 'look_left',
            'violation_timestamp' => now(),
            'screenshot_path' => 'screenshots/violations/'.$attempt->id.'/bukti.jpg',
        ]);
    }

    public function test_enrolled_student_and_owner_can_open_document_but_outsiders_cannot(): void
    {
        $lesson = $this->documentLesson();

        $this->actingAs($this->student)->get(route('files.lesson.document', $lesson))->assertOk();
        $this->actingAs($this->owner)->get(route('files.lesson.document', $lesson))->assertOk();
        $this->actingAs($this->makeStudent())->get(route('files.lesson.document', $lesson))->assertForbidden();
        $this->actingAs($this->makeInstructor())->get(route('files.lesson.document', $lesson))->assertForbidden();
    }

    public function test_document_lesson_content_uses_the_wide_doc_viewer(): void
    {
        $lesson = $this->documentLesson();

        $html = $this->actingAs($this->student)
            ->getJson(route('student.lessons.content', $lesson))
            ->assertOk()
            ->json('html');

        $this->assertStringContainsString('data-doc-viewer', $html);
    }

    public function test_document_in_locked_module_is_forbidden_for_student(): void
    {
        $this->module->update(['points_required' => 100]);
        $lesson = $this->documentLesson();

        $this->actingAs($this->student)->get(route('files.lesson.document', $lesson))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $lesson = $this->documentLesson();

        $this->get(route('files.lesson.document', $lesson))->assertRedirect();
    }

    public function test_submission_is_visible_to_owner_student_and_course_instructor_only(): void
    {
        $submission = $this->submission();

        $this->actingAs($this->student)->get(route('files.submission', $submission))->assertOk();
        $this->actingAs($this->owner)->get(route('files.submission', $submission))->assertOk();
        $this->actingAs($this->makeStudent())->get(route('files.submission', $submission))->assertForbidden();
        $this->actingAs($this->makeInstructor())->get(route('files.submission', $submission))->assertForbidden();
    }

    public function test_screenshot_is_visible_to_course_instructor_only(): void
    {
        $log = $this->monitoringLog();

        $this->actingAs($this->owner)->get(route('files.monitoring.screenshot', $log))->assertOk();
        $this->actingAs($this->makeAdmin())->get(route('files.monitoring.screenshot', $log))->assertOk();
        $this->actingAs($this->student)->get(route('files.monitoring.screenshot', $log))->assertForbidden();
        $this->actingAs($this->makeInstructor())->get(route('files.monitoring.screenshot', $log))->assertForbidden();
    }

    public function test_legacy_files_still_on_public_disk_are_served(): void
    {
        $lesson = $this->documentLesson('public');

        $this->actingAs($this->student)->get(route('files.lesson.document', $lesson))->assertOk();
    }

    public function test_move_command_moves_files_and_is_idempotent(): void
    {
        $lesson = $this->documentLesson('public');
        $submission = $this->submission('public');
        $log = $this->monitoringLog('public');

        Artisan::call('files:move-private', ['--dry-run' => true]);
        Storage::disk('public')->assertExists($submission->file_path);
        Storage::disk('local')->assertMissing($submission->file_path);

        Artisan::call('files:move-private');
        foreach ([$lesson->lessonable->file_path, $submission->file_path, $log->screenshot_path] as $path) {
            Storage::disk('local')->assertExists($path);
            Storage::disk('public')->assertMissing($path);
        }

        $this->assertSame(0, Artisan::call('files:move-private'));
        Storage::disk('local')->assertExists($submission->file_path);
    }

    public function test_new_assignment_submission_is_stored_on_private_disk(): void
    {
        $assignment = LessonAssignment::create(['instructions' => 'Kumpulkan', 'pass_mark' => 70]);
        Lesson::factory()->create([
            'module_id' => $this->module->id,
            'lessonable_type' => LessonAssignment::class,
            'lessonable_id' => $assignment->id,
        ]);

        $this->actingAs($this->student)->post(route('student.assignment.submit', $assignment), [
            'submission_file' => \Illuminate\Http\UploadedFile::fake()->create('tugas.pdf', 50, 'application/pdf'),
        ])->assertSessionHas('success');

        $path = AssignmentSubmission::where('user_id', $this->student->id)->value('file_path');
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_files_are_cached_privately_by_the_browser_for_a_day(): void
    {
        $lesson = $this->documentLesson();

        $response = $this->actingAs($this->student)->get(route('files.lesson.document', $lesson))->assertOk();

        $this->assertStringContainsString('max-age=86400', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
    }

    public function test_submission_of_a_deleted_assignment_lesson_returns_404_not_500(): void
    {
        $submission = $this->submission();
        Lesson::where('lessonable_type', LessonAssignment::class)->delete();

        $this->actingAs($this->owner)->get(route('files.submission', $submission))->assertNotFound();
    }

    public function test_screenshot_of_a_deleted_quiz_lesson_returns_404_not_500(): void
    {
        $log = $this->monitoringLog();
        Lesson::where('lessonable_type', Quiz::class)->delete();

        $this->actingAs($this->makeAdmin())->get(route('files.monitoring.screenshot', $log))->assertNotFound();
    }

    public function test_submission_and_screenshot_are_never_cached(): void
    {
        $submission = $this->submission();
        $log = $this->monitoringLog();

        foreach ([route('files.submission', $submission), route('files.monitoring.screenshot', $log)] as $url) {
            $cacheControl = $this->actingAs($this->owner)->get($url)->assertOk()->headers->get('Cache-Control');

            $this->assertStringContainsString('no-store', $cacheControl);
            $this->assertStringNotContainsString('max-age=86400', $cacheControl);
            $this->assertStringNotContainsString('public', $cacheControl);
        }
    }
}

<?php

namespace Tests\Feature\Authorization;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use App\Models\LessonPolling;
use App\Models\LessonPollingOption;
use App\Models\Module;
use App\Models\PointHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentActionEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $instructor;

    protected User $student;

    protected Course $course;

    protected Module $module;

    protected Lesson $assignmentLesson;

    protected LessonAssignment $assignment;

    protected Lesson $pollingLesson;

    protected LessonPolling $polling;

    protected LessonPollingOption $pollingOption;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();

        Storage::fake('public');

        $this->instructor = $this->makeInstructor();
        $this->student = $this->makeStudent();

        $category = CourseCategory::factory()->create();

        $this->course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $category->id,
            'status' => 'published',
        ]);

        $this->module = Module::factory()->create([
            'course_id' => $this->course->id,
        ]);

        $this->assignment = LessonAssignment::create([
            'instructions' => 'Submit your project here',
            'pass_mark' => 70,
        ]);

        $this->assignmentLesson = Lesson::factory()->create([
            'module_id' => $this->module->id,
            'title' => 'Assignment Lesson',
            'lessonable_id' => $this->assignment->id,
            'lessonable_type' => LessonAssignment::class,
        ]);

        $this->polling = LessonPolling::create([
            'question' => 'What is your favorite color?',
            'is_active' => true,
            'allow_multiple' => false,
            'show_results' => true,
        ]);

        $this->pollingOption = LessonPollingOption::create([
            'lesson_polling_id' => $this->polling->id,
            'text' => 'Blue',
            'order' => 1,
        ]);

        $this->pollingLesson = Lesson::factory()->create([
            'module_id' => $this->module->id,
            'title' => 'Polling Lesson',
            'lessonable_id' => $this->polling->id,
            'lessonable_type' => LessonPolling::class,
        ]);
    }

    public function test_non_enrolled_student_gets_403_on_assignment_submit_and_no_points_awarded(): void
    {
        $initialPointCount = PointHistory::count();
        $file = UploadedFile::fake()->create('project.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->student)
            ->withSession(['active_role' => 'student'])
            ->post(route('student.assignment.submit', $this->assignment), [
                'submission_file' => $file,
            ]);

        $response->assertStatus(403);
        $this->assertEquals($initialPointCount, PointHistory::count());
    }

    public function test_non_enrolled_student_gets_403_on_polling_submit_and_no_points_awarded(): void
    {
        $initialPointCount = PointHistory::count();

        $response = $this->actingAs($this->student)
            ->withSession(['active_role' => 'student'])
            ->postJson(route('student.lessons.polling.submit', $this->pollingLesson), [
                'polling_option_id' => $this->pollingOption->id,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akses ditolak.',
            ]);

        $this->assertEquals($initialPointCount, PointHistory::count());
    }

    public function test_enrolled_student_can_submit_assignment_and_polling(): void
    {
        $this->enroll($this->student, $this->course);

        // Assignment submission
        $file = UploadedFile::fake()->create('project.pdf', 100, 'application/pdf');
        $responseAssignment = $this->actingAs($this->student)
            ->withSession(['active_role' => 'student'])
            ->post(route('student.assignment.submit', $this->assignment), [
                'submission_file' => $file,
            ]);

        $responseAssignment->assertStatus(302);
        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->student->id,
        ]);

        // Polling submission
        $responsePolling = $this->actingAs($this->student)
            ->withSession(['active_role' => 'student'])
            ->postJson(route('student.lessons.polling.submit', $this->pollingLesson), [
                'polling_option_id' => $this->pollingOption->id,
            ]);

        $responsePolling->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('lesson_polling_responses', [
            'user_id' => $this->student->id,
            'lesson_polling_option_id' => $this->pollingOption->id,
        ]);
    }
}

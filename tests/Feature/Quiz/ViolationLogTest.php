<?php

namespace Tests\Feature\Quiz;

use App\Models\Lesson;
use App\Models\MonitoringLog;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizSecuritySetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class ViolationLogTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private $student;

    private QuizAttempt $attempt;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $course = $this->makeCourse($this->makeInstructor());
        $module = $this->makeModule($course);
        $quiz = Quiz::factory()->create();
        Lesson::factory()->create([
            'module_id' => $module->id,
            'lessonable_type' => Quiz::class,
            'lessonable_id' => $quiz->id,
        ]);
        QuizSecuritySetting::create([
            'quiz_id' => $quiz->id,
            'enable_camera_detection' => true,
            'enable_tab_detection' => true,
            'camera_violation_threshold' => 100,
            'tab_violation_threshold' => 100,
        ]);

        $this->student = $this->makeStudent();
        $this->enroll($this->student, $course);
        $this->attempt = QuizAttempt::factory()->create(['quiz_id' => $quiz->id, 'student_id' => $this->student->id]);
    }

    public function test_non_image_screenshot_is_not_stored_but_violation_is_logged(): void
    {
        $response = $this->actingAs($this->student)->post(route('student.quiz.log_camera_violation', $this->attempt), [
            'violation_type' => 'look_left',
            'screenshot' => UploadedFile::fake()->create('bukti.txt', 5, 'text/plain'),
        ]);

        $response->assertOk();
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertSame(1, MonitoringLog::where('attempt_id', $this->attempt->id)->count());
        $this->assertNull(MonitoringLog::where('attempt_id', $this->attempt->id)->value('screenshot_path'));
    }

    public function test_oversized_screenshot_is_not_stored(): void
    {
        $this->actingAs($this->student)->post(route('student.quiz.log_camera_violation', $this->attempt), [
            'violation_type' => 'look_left',
            'screenshot' => UploadedFile::fake()->image('besar.jpg')->size(2048),
        ])->assertOk();

        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_valid_screenshot_is_stored(): void
    {
        $this->actingAs($this->student)->post(route('student.quiz.log_camera_violation', $this->attempt), [
            'violation_type' => 'look_left',
            'screenshot' => UploadedFile::fake()->image('bukti.jpg')->size(100),
        ])->assertOk();

        $this->assertCount(1, Storage::disk('local')->allFiles());
        $this->assertNotNull(MonitoringLog::where('attempt_id', $this->attempt->id)->value('screenshot_path'));
    }

    public function test_violation_endpoints_are_throttled(): void
    {
        $statuses = [];
        for ($i = 0; $i < 31; $i++) {
            $statuses[] = $this->actingAs($this->student)
                ->postJson(route('student.quiz.log_tab_violation', $this->attempt))
                ->status();
        }

        $this->assertSame(429, end($statuses));
        $this->assertNotContains(429, array_slice($statuses, 0, 30));
    }
}

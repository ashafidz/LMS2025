<?php

namespace Tests\Feature\Files;

use App\Models\AssignmentSubmission;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use App\Models\LessonDocument;
use App\Models\LessonVideo;
use App\Models\MonitoringLog;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class CourseMediaCleanupTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private $instructor;

    private $student;

    private $course;

    private $module;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $this->instructor = $this->makeInstructor();
        $this->student = $this->makeStudent();
        $this->course = $this->makeCourse($this->instructor, [
            'thumbnail_url' => 'thumbnails/course-thumb.jpg',
        ]);
        Storage::disk('public')->put('thumbnails/course-thumb.jpg', 'jpgdata');
        $this->module = $this->makeModule($this->course);
        $this->enroll($this->student, $this->course);
    }

    private function seedCourseMedia(Course $course, $module, string $prefix = 'c1'): array
    {
        $docPath = "lesson_documents/{$prefix}-materi.pdf";
        Storage::disk('local')->put($docPath, '%PDF-1.4 materi');
        $doc = LessonDocument::create(['file_path' => $docPath]);
        $docLesson = Lesson::factory()->create([
            'module_id' => $module->id,
            'lessonable_type' => LessonDocument::class,
            'lessonable_id' => $doc->id,
        ]);

        $videoPath = "lesson_videos/{$prefix}-video.mp4";
        Storage::disk('public')->put($videoPath, 'mp4data');
        $video = LessonVideo::create(['source_type' => 'upload', 'video_path' => $videoPath]);
        $videoLesson = Lesson::factory()->create([
            'module_id' => $module->id,
            'lessonable_type' => LessonVideo::class,
            'lessonable_id' => $video->id,
        ]);

        $assignment = LessonAssignment::create(['instructions' => 'Kumpulkan tugas', 'pass_mark' => 70]);
        $assignmentLesson = Lesson::factory()->create([
            'module_id' => $module->id,
            'lessonable_type' => LessonAssignment::class,
            'lessonable_id' => $assignment->id,
        ]);
        $submissionPath = "assignment_submissions/{$assignment->id}/{$this->student->id}/{$prefix}-tugas.pdf";
        Storage::disk('local')->put($submissionPath, '%PDF-1.4 tugas');
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $this->student->id,
            'file_path' => $submissionPath,
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $quiz = Quiz::factory()->create();
        $quizLesson = Lesson::factory()->create([
            'module_id' => $module->id,
            'lessonable_type' => Quiz::class,
            'lessonable_id' => $quiz->id,
        ]);
        $attempt = QuizAttempt::factory()->create(['quiz_id' => $quiz->id, 'student_id' => $this->student->id]);
        $screenshotPath = "screenshots/violations/{$attempt->id}/{$prefix}-violation.jpg";
        Storage::disk('local')->put($screenshotPath, 'jpegdata');
        $log = MonitoringLog::create([
            'attempt_id' => $attempt->id,
            'violation_type' => 'look_left',
            'violation_timestamp' => now(),
            'screenshot_path' => $screenshotPath,
        ]);

        $certCode = "LMS-{$course->id}-{$this->student->id}-100";
        $certificate = Certificate::create([
            'user_id' => $this->student->id,
            'course_id' => $course->id,
            'certificate_code' => $certCode,
            'issued_at' => now(),
        ]);
        $cachedCertPath = "certificates/{$certCode}-hash123.pdf";
        Storage::disk('local')->put($cachedCertPath, '%PDF-1.4 cert');

        return compact(
            'docPath',
            'docLesson',
            'videoPath',
            'videoLesson',
            'assignmentLesson',
            'submissionPath',
            'submission',
            'quizLesson',
            'screenshotPath',
            'log',
            'certificate',
            'cachedCertPath'
        );
    }

    public function test_deleting_course_soft_deletes_db_record_and_removes_all_related_media_files(): void
    {
        $media = $this->seedCourseMedia($this->course, $this->module);

        $this->actingAs($this->instructor)
            ->delete(route('instructor.courses.destroy', $this->course))
            ->assertRedirect(route('instructor.courses.index'));

        // Course tetap soft-deleted di DB dan record sertifikat/submission tetap ada di DB
        $this->assertSoftDeleted('courses', ['id' => $this->course->id]);
        $this->assertDatabaseHas('certificates', ['id' => $media['certificate']->id]);
        $this->assertDatabaseHas('assignment_submissions', ['id' => $media['submission']->id]);

        // Semua file fisik di disk telah dihapus
        Storage::disk('public')->assertMissing('thumbnails/course-thumb.jpg');
        Storage::disk('local')->assertMissing($media['docPath']);
        Storage::disk('public')->assertMissing($media['videoPath']);
        Storage::disk('local')->assertMissing($media['submissionPath']);
        Storage::disk('local')->assertMissing($media['screenshotPath']);
        Storage::disk('local')->assertMissing($media['cachedCertPath']);
    }

    public function test_deleting_course_preserves_shared_document_and_video_of_active_cloned_course(): void
    {
        $media = $this->seedCourseMedia($this->course, $this->module);

        $clonedCourse = $this->makeCourse($this->instructor, ['title' => 'Salinan', 'slug' => 'salinan']);
        $clonedModule = $this->makeModule($clonedCourse);

        $clonedDoc = LessonDocument::create(['file_path' => $media['docPath']]);
        Lesson::factory()->create([
            'module_id' => $clonedModule->id,
            'lessonable_type' => LessonDocument::class,
            'lessonable_id' => $clonedDoc->id,
        ]);

        $clonedVideo = LessonVideo::create(['source_type' => 'upload', 'video_path' => $media['videoPath']]);
        Lesson::factory()->create([
            'module_id' => $clonedModule->id,
            'lessonable_type' => LessonVideo::class,
            'lessonable_id' => $clonedVideo->id,
        ]);

        $this->actingAs($this->instructor)
            ->delete(route('instructor.courses.destroy', $this->course))
            ->assertRedirect(route('instructor.courses.index'));

        // File dokumen dan video yang dipakai kursus salinan aktif tetap aman
        Storage::disk('local')->assertExists($media['docPath']);
        Storage::disk('public')->assertExists($media['videoPath']);

        // Namun file submission dan screenshot milik kursus yang dihapus tetap dibersihkan
        Storage::disk('local')->assertMissing($media['submissionPath']);
        Storage::disk('local')->assertMissing($media['screenshotPath']);

        // Ketika kursus salinan ikut dihapus, barulah file dokumen dan video tersebut dihapus
        $this->actingAs($this->instructor)
            ->delete(route('instructor.courses.destroy', $clonedCourse))
            ->assertRedirect(route('instructor.courses.index'));

        Storage::disk('local')->assertMissing($media['docPath']);
        Storage::disk('public')->assertMissing($media['videoPath']);
    }

    public function test_deleting_module_cleans_up_all_lesson_media_files(): void
    {
        $media = $this->seedCourseMedia($this->course, $this->module);

        $this->actingAs($this->instructor)
            ->delete(route('instructor.modules.destroy', $this->module))
            // 2.0.0: after deleting a module the instructor lands on the course list with that course's modules open
            ->assertRedirect(route('instructor.courses.index', ['open' => $this->course->getRouteKey()]).'#course-'.$this->course->getRouteKey());

        Storage::disk('local')->assertMissing($media['docPath']);
        Storage::disk('public')->assertMissing($media['videoPath']);
        Storage::disk('local')->assertMissing($media['submissionPath']);
        Storage::disk('local')->assertMissing($media['screenshotPath']);
    }

    public function test_deleting_assignment_or_quiz_lesson_cleans_up_submissions_and_screenshots(): void
    {
        $media = $this->seedCourseMedia($this->course, $this->module);

        $this->actingAs($this->instructor)
            ->delete(route('instructor.lessons.destroy', $media['assignmentLesson']));
        Storage::disk('local')->assertMissing($media['submissionPath']);

        $this->actingAs($this->instructor)
            ->delete(route('instructor.lessons.destroy', $media['quizLesson']));
        Storage::disk('local')->assertMissing($media['screenshotPath']);
    }

    public function test_prune_course_media_command_cleans_up_files_from_previously_soft_deleted_courses(): void
    {
        $media = $this->seedCourseMedia($this->course, $this->module);

        // Kursus aktif lain yang tidak boleh tersentuh
        $activeCourse = $this->makeCourse($this->instructor, [
            'title' => 'Kursus Aktif',
            'slug' => 'kursus-aktif',
            'thumbnail_url' => 'thumbnails/active-thumb.jpg',
        ]);
        Storage::disk('public')->put('thumbnails/active-thumb.jpg', 'jpgdata');
        $activeModule = $this->makeModule($activeCourse);
        $activeMedia = $this->seedCourseMedia($activeCourse, $activeModule, 'active');

        // Simulasikan kursus lama yang sudah di-soft-delete sebelum perbaikan ini ada
        $this->course->delete();

        Artisan::call('files:prune-course-media', ['--dry-run' => true]);
        Storage::disk('local')->assertExists($media['docPath']);
        Storage::disk('local')->assertExists($media['submissionPath']);

        $this->assertSame(0, Artisan::call('files:prune-course-media'));

        // File milik kursus yang sudah soft-deleted bersih
        Storage::disk('public')->assertMissing('thumbnails/course-thumb.jpg');
        Storage::disk('local')->assertMissing($media['docPath']);
        Storage::disk('public')->assertMissing($media['videoPath']);
        Storage::disk('local')->assertMissing($media['submissionPath']);
        Storage::disk('local')->assertMissing($media['screenshotPath']);
        Storage::disk('local')->assertMissing($media['cachedCertPath']);

        // File milik kursus aktif tetap utuh
        Storage::disk('public')->assertExists('thumbnails/active-thumb.jpg');
        Storage::disk('local')->assertExists($activeMedia['docPath']);
        Storage::disk('public')->assertExists($activeMedia['videoPath']);
        Storage::disk('local')->assertExists($activeMedia['submissionPath']);
        Storage::disk('local')->assertExists($activeMedia['screenshotPath']);
        Storage::disk('local')->assertExists($activeMedia['cachedCertPath']);
    }
}

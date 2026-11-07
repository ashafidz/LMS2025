<?php

namespace Tests\Feature\Instructor;

use App\Models\Lesson;
use App\Models\LessonDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class LessonDocumentUpdateTest extends TestCase
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
        $this->course = $this->makeCourse($this->instructor);
        $this->module = $this->makeModule($this->course);
        $this->enroll($this->student, $this->course);
    }

    private function createDocumentLesson(string $path = 'lesson_documents/old-doc.pdf'): Lesson
    {
        Storage::disk('local')->put($path, '%PDF-1.4 old document');
        $doc = LessonDocument::create(['file_path' => $path]);

        return Lesson::factory()->create([
            'module_id' => $this->module->id,
            'title' => 'Dokumen Lama',
            'lessonable_type' => LessonDocument::class,
            'lessonable_id' => $doc->id,
        ]);
    }

    public function test_instructor_can_update_lesson_document_and_preview_url_changes_version(): void
    {
        $lesson = $this->createDocumentLesson('lesson_documents/old-doc.pdf');
        $oldHash = substr(md5('lesson_documents/old-doc.pdf'), 0, 8);

        $beforeHtml = $this->actingAs($this->student)
            ->getJson(route('student.lessons.content', $lesson))
            ->assertOk()
            ->json('html');
        $this->assertStringContainsString('v='.$oldHash, $beforeHtml);

        $newPdf = UploadedFile::fake()->create('new-doc.pdf', 120, 'application/pdf');

        $this->actingAs($this->instructor)
            ->put(route('instructor.lessons.update', $lesson), [
                'title' => 'Dokumen Baru',
                'document_file' => $newPdf,
            ])
            ->assertRedirect(route('instructor.modules.lessons.index', $this->module))
            ->assertSessionHas('success');

        $lesson->refresh();
        $newPath = $lesson->lessonable->file_path;
        $newHash = substr(md5($newPath), 0, 8);

        $this->assertSame('Dokumen Baru', $lesson->title);
        $this->assertNotSame('lesson_documents/old-doc.pdf', $newPath);
        Storage::disk('local')->assertMissing('lesson_documents/old-doc.pdf');
        Storage::disk('local')->assertExists($newPath);

        $afterHtml = $this->actingAs($this->student)
            ->getJson(route('student.lessons.content', $lesson))
            ->assertOk()
            ->json('html');
        $this->assertStringContainsString('v='.$newHash, $afterHtml);
        $this->assertStringNotContainsString('v='.$oldHash, $afterHtml);

        $editPage = $this->actingAs($this->instructor)
            ->get(route('instructor.lessons.edit', $lesson))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('v='.$newHash, $editPage);
    }

    public function test_updating_document_preserves_old_file_if_still_used_by_cloned_course(): void
    {
        $lesson = $this->createDocumentLesson('lesson_documents/shared-doc.pdf');

        $clonedCourse = $this->makeCourse($this->instructor, ['title' => 'Kursus Salinan', 'slug' => 'kursus-salinan']);
        $clonedModule = $this->makeModule($clonedCourse);
        $clonedDoc = LessonDocument::create(['file_path' => 'lesson_documents/shared-doc.pdf']);
        Lesson::factory()->create([
            'module_id' => $clonedModule->id,
            'title' => 'Dokumen Salinan',
            'lessonable_type' => LessonDocument::class,
            'lessonable_id' => $clonedDoc->id,
        ]);

        $this->actingAs($this->instructor)
            ->put(route('instructor.lessons.update', $lesson), [
                'title' => 'Dokumen Diperbarui',
                'document_file' => UploadedFile::fake()->create('updated.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('instructor.modules.lessons.index', $this->module));

        $lesson->refresh();
        Storage::disk('local')->assertExists('lesson_documents/shared-doc.pdf');
        Storage::disk('local')->assertExists($lesson->lessonable->file_path);
    }

    public function test_failed_php_upload_or_invalid_file_returns_validation_error_instead_of_silently_succeeding(): void
    {
        $lesson = $this->createDocumentLesson('lesson_documents/old-doc.pdf');

        // Simulasikan file yang gagal diunggah di level PHP (UPLOAD_ERR_INI_SIZE, tmp_name kosong)
        $failedPhpUpload = new SymfonyUploadedFile('', 'terlalu-besar.pdf', 'application/pdf', UPLOAD_ERR_INI_SIZE, true);

        $this->actingAs($this->instructor)
            ->from(route('instructor.lessons.edit', $lesson))
            ->put(route('instructor.lessons.update', $lesson), [
                'title' => 'Dokumen Gagal Upload',
                'document_file' => UploadedFile::createFromBase($failedPhpUpload),
            ])
            ->assertRedirect(route('instructor.lessons.edit', $lesson))
            ->assertSessionHasErrors('document_file');

        $lesson->refresh();
        $this->assertSame('lesson_documents/old-doc.pdf', $lesson->lessonable->file_path);
        Storage::disk('local')->assertExists('lesson_documents/old-doc.pdf');
    }
}

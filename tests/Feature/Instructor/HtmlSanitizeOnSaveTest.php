<?php

namespace Tests\Feature\Instructor;

use App\Models\LessonArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class HtmlSanitizeOnSaveTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    public function test_article_content_is_sanitized_on_create_and_update(): void
    {
        $owner = $this->makeInstructor();
        $course = $this->makeCourse($owner);
        $module = $this->makeModule($course);
        $dirty = '<p>Materi</p><script>alert(1)</script><img src="x.png" onerror="alert(1)">';

        $this->actingAs($owner)->post(route('instructor.modules.lessons.store', $module), [
            'title' => 'Artikel',
            'lesson_type' => 'article',
            'content' => $dirty,
        ]);

        $article = LessonArticle::firstOrFail();
        $this->assertStringContainsString('Materi', $article->content);
        $this->assertStringNotContainsString('<script', $article->content);
        $this->assertStringNotContainsString('onerror', $article->content);

        $lesson = $module->lessons()->firstOrFail();
        $this->actingAs($owner)->put(route('instructor.lessons.update', $lesson), [
            'title' => 'Artikel',
            'content' => '<p onclick="x()">Baru</p><script>1</script>',
        ]);

        $article->refresh();
        $this->assertStringContainsString('Baru', $article->content);
        $this->assertStringNotContainsString('onclick', $article->content);
        $this->assertStringNotContainsString('<script', $article->content);
    }

    public function test_article_preview_sanitizes_legacy_dirty_content_on_render(): void
    {
        $owner = $this->makeInstructor();
        $course = $this->makeCourse($owner);
        $lesson = $this->makeArticleLesson($this->makeModule($course));
        $lesson->lessonable->update(['content' => '<p>Aman</p><script>alert("xss")</script>']);
        $student = $this->makeStudent();
        $this->enroll($student, $course);

        $response = $this->actingAs($student)->getJson(route('student.lessons.content', $lesson));

        $response->assertOk();
        $this->assertStringContainsString('Aman', $response->json('html'));
        $this->assertStringNotContainsString('alert("xss")', $response->json('html'));
    }

    public function test_course_description_is_sanitized_when_updated(): void
    {
        $owner = $this->makeInstructor();
        $course = $this->makeCourse($owner);

        $this->actingAs($owner)->put(route('instructor.courses.update', $course), [
            'title' => $course->title,
            'category_id' => $course->category_id,
            'description' => '<p>Deskripsi</p><script>alert(1)</script>',
            'availability_type' => 'lifetime',
            'payment_type' => $course->payment_type,
        ]);

        $course->refresh();
        $this->assertStringContainsString('Deskripsi', $course->description);
        $this->assertStringNotContainsString('<script', $course->description);
    }
}

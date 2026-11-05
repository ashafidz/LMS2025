<?php

namespace Tests\Feature\Instructor;

use App\Models\LessonArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLessonFixtures;
use Tests\TestCase;

class ArticleEditorTest extends TestCase
{
    use BuildsLessonFixtures;
    use RefreshDatabase;

    private function assertSelfHostedEditor($response): void
    {
        $response->assertOk()
            ->assertSee('vendor/jodit/jodit.min.js', false)
            ->assertDontSee('tiny.cloud', false)
            ->assertDontSee('tinymce', false);
    }

    public function test_create_article_page_loads_self_hosted_jodit_not_tinymce(): void
    {
        $owner = $this->makeInstructor();
        $module = $this->makeModule($this->makeCourse($owner));

        $this->assertSelfHostedEditor(
            $this->actingAs($owner)->withSession(['active_role' => 'instructor'])
                ->get(route('instructor.modules.lessons.create', [$module, 'type' => 'article']))
        );
    }

    public function test_edit_article_page_loads_self_hosted_jodit_not_tinymce(): void
    {
        $owner = $this->makeInstructor();
        $lesson = $this->makeArticleLesson($this->makeModule($this->makeCourse($owner)));

        $this->assertSelfHostedEditor(
            $this->actingAs($owner)->withSession(['active_role' => 'instructor'])
                ->get(route('instructor.lessons.edit', $lesson))
        );
    }

    public function test_jodit_style_html_survives_the_server_sanitizer(): void
    {
        $owner = $this->makeInstructor();
        $module = $this->makeModule($this->makeCourse($owner));
        $html = '<p style="text-align:center">Judul</p>'
            .'<table style="border-collapse:collapse;width:100%"><tbody><tr><td style="width:50%">A</td><td>B</td></tr></tbody></table>'
            .'<p><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==" alt="gambar"></p>'
            .'<p><a href="https://x.test">tautan</a></p>';

        $this->actingAs($owner)->post(route('instructor.modules.lessons.store', $module), [
            'title' => 'Artikel Jodit',
            'lesson_type' => 'article',
            'content' => $html,
        ]);

        $content = LessonArticle::firstOrFail()->content;
        $this->assertStringContainsString('<table', $content);
        $this->assertStringContainsString('text-align:center', $content);
        $this->assertStringContainsString('data:image/png', $content);
        $this->assertStringContainsString('href="https://x.test"', $content);
    }
}

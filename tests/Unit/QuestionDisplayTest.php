<?php

namespace Tests\Unit;

use App\Models\Question;
use App\Models\QuestionOption;
use Tests\TestCase;

class QuestionDisplayTest extends TestCase
{
    private function question(string $text, string $format = 'plain', string $type = 'multiple_choice_single'): Question
    {
        return Question::make([
            'question_text' => $text,
            'question_type' => $type,
            'text_format' => $format,
        ]);
    }

    public function test_plain_text_renders_like_the_old_output(): void
    {
        $html = $this->question("x < 5 & y\nbaris")->displayText();

        $this->assertSame("x &lt; 5 &amp; y<br />\nbaris", $html);
    }

    public function test_plain_blank_marker_becomes_underscores(): void
    {
        $this->assertSame('Ibu kota ___', $this->question('Ibu kota [[BLANK_1]]', 'plain', 'drag_and_drop')->displayText());
    }

    public function test_html_text_is_sanitized_and_blank_marker_replaced(): void
    {
        $html = $this->question('<p>Ibu kota [[BLANK_1]]</p><script>x</script>', 'html', 'drag_and_drop')->displayText();

        $this->assertStringContainsString('<p>Ibu kota ___</p>', $html);
        $this->assertStringNotContainsString('<script', $html);
    }

    public function test_text_with_blanks_keeps_old_plain_output_and_calls_the_renderer(): void
    {
        $plain = $this->question('A < B [[BLANK_1]] C', 'plain', 'drag_and_drop')
            ->displayTextWithBlanks(fn ($id) => "<select data-id=\"$id\"></select>");
        $html = $this->question('<p>[[BLANK_2]]</p><script>x</script>', 'html', 'drag_and_drop')
            ->displayTextWithBlanks(fn ($id) => "<select data-id=\"$id\"></select>");

        $this->assertSame('A &lt; B <select data-id="BLANK_1"></select> C', $plain);
        $this->assertStringContainsString('<p><select data-id="BLANK_2"></select></p>', $html);
        $this->assertStringNotContainsString('<script', $html);
    }

    public function test_display_option_is_html_only_for_multiple_choice(): void
    {
        $option = QuestionOption::make(['option_text' => '<p><strong>Ya</strong></p><script>x</script>']);

        $rich = $this->question('t', 'html', 'multiple_choice_multiple')->displayOption($option);
        $this->assertStringContainsString('<strong>Ya</strong>', $rich);
        $this->assertStringNotContainsString('<script', $rich);

        foreach (['true_false', 'drag_and_drop'] as $type) {
            $escaped = $this->question('t', 'html', $type)->displayOption($option);
            $this->assertStringContainsString('&lt;strong&gt;Ya&lt;/strong&gt;', $escaped);
            $this->assertStringNotContainsString('<strong>', $escaped);
        }

        $plain = $this->question('t', 'plain', 'multiple_choice_single')->displayOption(QuestionOption::make(['option_text' => 'a < b']));
        $this->assertSame('a &lt; b', $plain);
    }

    public function test_display_explanation_is_null_when_empty(): void
    {
        $this->assertNull($this->question('t')->displayExplanation());
        $this->assertSame('a &lt; b<br />'."\n".'c', Question::make(['explanation' => "a < b\nc", 'text_format' => 'plain'])->displayExplanation());
    }

    public function test_editor_text_converts_plain_to_paragraphs(): void
    {
        $this->assertSame('<p>a</p><p>b<br>c</p>', $this->question("a\n\nb\nc")->editorText());
        $this->assertSame('', $this->question('')->editorText());
    }

    public function test_editor_text_returns_stored_html_untouched(): void
    {
        $this->assertSame('<p>Sudah <em>html</em></p>', $this->question('<p>Sudah <em>html</em></p>', 'html')->editorText());
    }

    public function test_editor_option_converts_only_for_multiple_choice(): void
    {
        $option = QuestionOption::make(['option_text' => 'a < b']);

        $this->assertSame('<p>a &lt; b</p>', $this->question('t', 'plain', 'multiple_choice_single')->editorOption($option));
        $this->assertSame('a < b', $this->question('t', 'plain', 'drag_and_drop')->editorOption($option));
    }

    public function test_plain_summary_decodes_entities_and_marks_image_only_questions(): void
    {
        $this->assertSame('A & B x', $this->question('<p>A &amp; B&nbsp;x</p>', 'html')->plainSummary(100));
        $this->assertSame('(gambar)', $this->question('<p><img src="/storage/question-images/1/a.png"></p>', 'html')->plainSummary(100));
        $this->assertSame('x < 5', $this->question('x < 5')->plainSummary(100));
    }

    public function test_plain_summary_keeps_words_apart_across_paragraphs_and_line_breaks(): void
    {
        $this->assertSame('x < 5 & y baris dua', $this->question('<p>x &lt; 5 &amp; y<br>baris dua</p>', 'html')->plainSummary(100));
        $this->assertSame('satu dua', $this->question('<p>satu</p><p>dua</p>', 'html')->plainSummary(100));
        $this->assertSame('Soal lama 7 < 9 baris dua', $this->question("Soal lama 7 < 9\nbaris dua")->plainSummary(100));
    }
}

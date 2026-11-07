<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use App\Traits\HasHashedRouteKey;
use App\Traits\HasLocalDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Question extends Model
{
    use HasFactory, SoftDeletes;
    use HasHashedRouteKey;
    use HasLocalDates;

    public const FORMAT_PLAIN = 'plain';

    public const FORMAT_HTML = 'html';

    public const TYPE_LABELS = [
        'multiple_choice_single' => 'Pilihan ganda (satu jawaban)',
        'multiple_choice_multiple' => 'Pilihan ganda (banyak jawaban)',
        'true_false' => 'Benar/Salah',
        'drag_and_drop' => 'Isian (drag and drop)',
    ];

    protected $fillable = [
        'topic_id',
        'question_text',
        'question_type',
        'text_format',
        'score',
        'explanation',
    ];

    /**
     * Question type as an Indonesian name (never show the raw code to users).
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->question_type] ?? ucfirst(str_replace('_', ' ', (string) $this->question_type));
    }

    public function isHtml(): bool
    {
        return $this->text_format === self::FORMAT_HTML;
    }

    /**
     * Option texts are rich only for the two multiple choice types. Other types keep plain inputs.
     */
    public function hasRichOptions(): bool
    {
        return $this->isHtml()
            && in_array($this->question_type, ['multiple_choice_single', 'multiple_choice_multiple'], true);
    }

    /**
     * Question text as safe HTML, with every [[BLANK_n]] marker shown as ___.
     */
    public function displayText(): string
    {
        $text = (string) $this->question_text;

        if ($this->isHtml()) {
            return preg_replace('/\[\[BLANK_\d+\]\]/', '___', HtmlSanitizer::clean($text));
        }

        return nl2br(e(preg_replace('/\[\[BLANK_\d+\]\]/', '___', $text)));
    }

    /**
     * Question text for the drag and drop take page. $renderBlank receives the blank id (BLANK_1).
     */
    public function displayTextWithBlanks(callable $renderBlank): string
    {
        $text = (string) $this->question_text;
        $safe = $this->isHtml() ? HtmlSanitizer::clean($text) : e($text);

        return preg_replace_callback('/\[\[(BLANK_\d+)\]\]/', fn ($m) => $renderBlank($m[1]), $safe);
    }

    public function displayExplanation(): ?string
    {
        $explanation = (string) $this->explanation;

        if (trim($explanation) === '') {
            return null;
        }

        return $this->isHtml() ? HtmlSanitizer::clean($explanation) : nl2br(e($explanation));
    }

    public function displayOption(QuestionOption $option): string
    {
        return $this->hasRichOptions()
            ? HtmlSanitizer::clean($option->option_text)
            : e($option->option_text);
    }

    /**
     * Value to put in an editor: stored HTML as is, or old plain text converted to paragraphs.
     */
    public function editorText(): string
    {
        return $this->toEditorHtml((string) $this->question_text);
    }

    public function editorExplanation(): string
    {
        return $this->toEditorHtml((string) $this->explanation);
    }

    public function editorOption(QuestionOption $option): string
    {
        if (! in_array($this->question_type, ['multiple_choice_single', 'multiple_choice_multiple'], true)) {
            return (string) $option->option_text;
        }

        return $this->toEditorHtml((string) $option->option_text);
    }

    /**
     * One-line plain summary for lists. Decodes entities and shows "(gambar)" for an image-only question.
     */
    public function plainSummary(int $limit = 100): string
    {
        $text = (string) $this->question_text;
        // Line and block breaks become spaces first, otherwise strip_tags glues "<p>a</p><p>b</p>" into "ab".
        $spaced = preg_replace('/<\/?(br|p|div|li|ul|ol|tr|td|th|h[1-6]|blockquote)\b[^>]*>/i', ' ', $text);
        $decoded = html_entity_decode(strip_tags($spaced), ENT_QUOTES);
        $decoded = trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $decoded)));

        if ($decoded === '' && stripos($text, '<img') !== false) {
            return '(gambar)';
        }

        return Str::limit($decoded, $limit);
    }

    private function toEditorHtml(string $value): string
    {
        if (trim($value) === '') {
            return '';
        }

        if ($this->isHtml()) {
            return $value;
        }

        $paragraphs = preg_split('/\R{2,}/', trim($value));

        return collect($paragraphs)
            ->map(fn ($para) => '<p>'.preg_replace('/\R/', '<br>', e($para)).'</p>')
            ->implode('');
    }

    /**
     * The topic this question belongs to.
     */
    public function topic()
    {
        return $this->belongsTo(QuestionTopic::class, 'topic_id');
    }

    /**
     * The options available for this question.
     */
    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    /**
     * The quizzes that include this question.
     */
    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'quiz_question');
    }
}

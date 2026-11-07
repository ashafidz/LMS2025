<?php

namespace Tests\Feature\Instructor;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuestionRichTextTest extends TestCase
{
    use RefreshDatabase;

    private User $instructor;

    private QuestionTopic $topic;

    protected function setUp(): void
    {
        parent::setUp();
        $this->instructor = $this->makeInstructor();
        $this->topic = QuestionTopic::create([
            'instructor_id' => $this->instructor->id,
            'name' => 'Topik Uji',
            'available_for_all_courses' => true,
        ]);
    }

    private function asInstructor(): static
    {
        return $this->actingAs($this->instructor)->withSession(['active_role' => 'instructor']);
    }

    private function store(array $overrides = [])
    {
        return $this->asInstructor()->post(route('instructor.question-bank.questions.store', $this->topic), array_merge([
            'question_type' => 'multiple_choice_single',
            'question_text' => '<p>Soal <strong>tebal</strong></p>',
            'score' => 10,
            'explanation' => '<p>Karena benar</p>',
            'options' => [
                ['text' => '<p>Pilihan A</p>', 'is_correct' => 1],
                ['text' => '<p>Pilihan B</p>', 'is_correct' => 0],
            ],
        ], $overrides));
    }

    private function plainQuestion(string $text = "x < 5\nbaris dua"): Question
    {
        $question = Question::create([
            'topic_id' => $this->topic->id,
            'question_text' => $text,
            'question_type' => 'multiple_choice_single',
            'score' => 10,
            'explanation' => 'a < b',
        ]);
        QuestionOption::create(['question_id' => $question->id, 'option_text' => 'a & b', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $question->id, 'option_text' => 'c', 'is_correct' => false]);

        return $question;
    }

    public function test_store_multiple_choice_saves_sanitized_html_and_marks_format(): void
    {
        $this->store([
            'question_text' => '<p>Lihat gambar</p><script>x</script><p><img src="/storage/question-images/1/abc.png" alt="a"></p>',
            'options' => [
                ['text' => '<p>A <u>garis</u></p><script>y</script>', 'is_correct' => 1],
                ['text' => '<p>B</p>', 'is_correct' => 0],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $question = Question::firstOrFail();
        $this->assertSame('html', $question->text_format);
        $this->assertStringNotContainsString('<script', $question->question_text);
        $this->assertStringContainsString('<img src="/storage/question-images/1/abc.png"', $question->question_text);
        $this->assertStringNotContainsString('<script', $question->options()->first()->option_text);
        $this->assertStringContainsString('<u>garis</u>', $question->options()->first()->option_text);
    }

    public function test_store_rejects_pasted_base64_images(): void
    {
        $this->store(['question_text' => '<p><img src="data:image/png;base64,AAAA"></p>'])
            ->assertSessionHasErrors('question_text');

        $this->assertSame(0, Question::count());
    }

    public function test_store_rejects_base64_image_in_an_option(): void
    {
        $this->store(['options' => [
            ['text' => '<p><img src="data:image/png;base64,AAAA"></p>', 'is_correct' => 1],
            ['text' => '<p>B</p>', 'is_correct' => 0],
        ]])->assertSessionHasErrors('options.0.text');
    }

    public function test_store_rejects_empty_rich_question_text(): void
    {
        $this->store(['question_text' => '<p>&nbsp;</p>'])
            ->assertSessionHasErrors(['question_text' => 'Teks pertanyaan tidak boleh kosong.']);
    }

    public function test_store_rejects_empty_rich_option(): void
    {
        $this->store(['options' => [
            ['text' => '<p>&nbsp;</p>', 'is_correct' => 1],
            ['text' => '<p>B</p>', 'is_correct' => 0],
        ]])->assertSessionHasErrors(['options.0.text' => 'Setiap pilihan jawaban harus diisi.']);
    }

    public function test_image_only_question_text_is_accepted(): void
    {
        $this->store(['question_text' => '<p><img src="/storage/question-images/1/a.png"></p>'])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Question::count());
    }

    public function test_drag_and_drop_rejects_marker_split_by_formatting(): void
    {
        $this->store([
            'question_type' => 'drag_and_drop',
            'question_text' => '<p>[[<strong>BLANK_1</strong>]]</p>',
            'options' => [['text' => 'Paris', 'gap_id' => 'BLANK_1'], ['text' => 'Roma', 'gap_id' => '']],
        ])->assertSessionHasErrors();

        $this->assertSame(0, Question::count());
    }

    public function test_drag_and_drop_accepts_intact_marker_and_keeps_options_plain(): void
    {
        $this->store([
            'question_type' => 'drag_and_drop',
            'question_text' => '<p>[[BLANK_1]]</p>',
            'options' => [['text' => 'Paris & Lyon', 'gap_id' => 'BLANK_1'], ['text' => 'Roma', 'gap_id' => '']],
        ])->assertSessionHasNoErrors();

        $question = Question::firstOrFail();
        $this->assertSame('html', $question->text_format);
        $this->assertSame('Paris & Lyon', $question->options()->orderBy('id')->first()->option_text);
    }

    public function test_drag_and_drop_rejects_gap_id_missing_from_text(): void
    {
        $this->store([
            'question_type' => 'drag_and_drop',
            'question_text' => '<p>[[BLANK_1]]</p>',
            'options' => [['text' => 'Paris', 'gap_id' => 'BLANK_9'], ['text' => 'Roma', 'gap_id' => '']],
        ])->assertSessionHasErrors('options.0.gap_id');
    }

    public function test_edit_page_shows_old_plain_question_converted_for_the_editor(): void
    {
        $question = $this->plainQuestion();

        $this->asInstructor()->get(route('instructor.question-bank.questions.edit', $question))
            ->assertOk()
            ->assertSee('&lt;p&gt;x &amp;lt; 5&lt;br&gt;baris dua&lt;/p&gt;', false)
            ->assertSee('data-rich="question"', false)
            ->assertSee('data-rich="option"', false);
    }

    public function test_updating_an_old_plain_question_saves_it_as_html(): void
    {
        $question = $this->plainQuestion();

        $this->asInstructor()->put(route('instructor.question-bank.questions.update', $question), [
            'question_text' => '<p>x &lt; 5</p><p>baris dua</p>',
            'score' => 10,
            'explanation' => '',
            'options' => [
                ['text' => '<p>a &amp; b</p>', 'is_correct' => 1],
                ['text' => '<p>c</p>', 'is_correct' => 0],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $question->refresh();
        $this->assertSame('html', $question->text_format);
        $this->assertNull($question->explanation);
        $this->assertStringContainsString('<p>baris dua</p>', $question->question_text);
    }

    public function test_plain_question_in_a_quiz_renders_exactly_like_before_on_take_page(): void
    {
        $student = $this->makeStudent();
        $course = Course::factory()->create(['instructor_id' => $this->instructor->id, 'status' => 'published']);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $quiz = Quiz::factory()->create();
        Lesson::factory()->create([
            'module_id' => $module->id,
            'lessonable_id' => $quiz->id,
            'lessonable_type' => Quiz::class,
        ]);
        $question = $this->plainQuestion();
        $quiz->questions()->attach($question->id, ['order' => 1]);
        $this->enroll($student, $course);
        $this->actingAs($student)->withSession(['active_role' => 'student'])
            ->post(route('student.quiz.begin', $quiz))->assertRedirect();
        $attempt = QuizAttempt::where('quiz_id', $quiz->id)->where('student_id', $student->id)->firstOrFail();

        $this->actingAs($student)->withSession(['active_role' => 'student'])
            ->get(route('student.quiz.take', $attempt))
            ->assertOk()
            ->assertSee('x &lt; 5<br />', false)
            ->assertSee('a &amp; b', false);
    }

    public function test_clone_appends_copy_marker_by_format(): void
    {
        $this->store()->assertSessionHasNoErrors();
        $html = Question::firstOrFail();
        $plain = $this->plainQuestion('Soal lama');

        $this->asInstructor()->post(route('instructor.question-bank.questions.clone', $html));
        $this->asInstructor()->post(route('instructor.question-bank.questions.clone', $plain));

        $this->assertTrue(Question::where('question_text', $html->question_text.'<p>(Copy)</p>')->exists());
        $this->assertTrue(Question::where('question_text', 'Soal lama (Copy)')->exists());
    }

    public function test_instructor_can_upload_a_png(): void
    {
        Storage::fake('public');

        $response = $this->asInstructor()->postJson(route('instructor.question-bank.images.store'), [
            'files' => [UploadedFile::fake()->image('gambar.png', 40, 40)],
        ]);

        $response->assertOk()->assertJsonPath('success', true)->assertJsonPath('data.isImages.0', true);
        $path = $response->json('data.files.0');
        $this->assertStringStartsWith('/storage/question-images/'.$this->instructor->id.'/', $path);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $path));
    }

    public function test_upload_explains_a_too_large_image(): void
    {
        Storage::fake('public');

        $this->asInstructor()->postJson(route('instructor.question-bank.images.store'), [
            'files' => [UploadedFile::fake()->image('foto.jpg')->size(3000)],
        ])->assertOk()->assertJsonPath('success', false)
            ->assertJsonPath('data.messages.0', 'Ukuran gambar maksimal 2 MB.');

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_upload_rejects_a_pdf(): void
    {
        Storage::fake('public');

        $this->asInstructor()->postJson(route('instructor.question-bank.images.store'), [
            'files' => [UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf')],
        ])->assertOk()->assertJsonPath('success', false)
            ->assertJsonPath('data.messages.0', 'File harus berupa gambar (JPG, PNG, atau GIF).');

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_student_cannot_upload(): void
    {
        Storage::fake('public');

        $this->actingAs($this->makeStudent())->withSession(['active_role' => 'student'])
            ->postJson(route('instructor.question-bank.images.store'), [
                'files' => [UploadedFile::fake()->image('a.png')],
            ])->assertForbidden();
    }

    public function test_guest_upload_redirects_to_login(): void
    {
        $this->post(route('instructor.question-bank.images.store'), [])->assertRedirect(route('login'));
    }

    public function test_validation_errors_are_shown_on_the_create_form_after_redirect(): void
    {
        $createUrl = route('instructor.question-bank.questions.create', [$this->topic, 'type' => 'drag_and_drop']);

        $this->asInstructor()->from($createUrl)->post(route('instructor.question-bank.questions.store', $this->topic), [
            'question_type' => 'drag_and_drop',
            'question_text' => '<p>[[<strong>BLANK_1</strong>]]</p>',
            'score' => 10,
            'options' => [['text' => 'Paris', 'gap_id' => 'BLANK_1'], ['text' => 'Roma', 'gap_id' => '']],
        ])->assertRedirect($createUrl);

        $this->asInstructor()->get($createUrl)
            ->assertOk()
            ->assertSee('Pertanyaan belum tersimpan')
            ->assertSee('tidak ditemukan utuh di teks pertanyaan');
    }

    public function test_error_messages_are_indonesian_without_raw_field_keys(): void
    {
        $response = $this->store(['options' => [
            ['text' => '', 'is_correct' => 1],
            ['text' => '<p>B</p>', 'is_correct' => 0],
        ]]);

        $message = session('errors')->get('options.0.text')[0];
        $this->assertStringNotContainsString('options.0.text', $message);
        $this->assertStringContainsString('Teks pilihan jawaban', $message);
        $this->assertStringContainsString('wajib diisi', $message);
        $response->assertSessionHasErrors('options.0.text');
    }

    public function test_size_rule_messages_are_indonesian(): void
    {
        $this->store(['score' => 0])->assertSessionHasErrors(['score' => 'Nilai minimal 1.']);
        $this->store(['options' => [['text' => '<p>Satu</p>', 'is_correct' => 1]]])
            ->assertSessionHasErrors(['options' => 'Isi minimal 2 Pilihan jawaban.']);
    }

    public function test_entity_encoded_data_image_is_rejected(): void
    {
        // A valid 1x1 PNG: the sanitizer keeps data images of this kind, so only the entity-decoded check catches it.
        $png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $this->store(['question_text' => '<p><img src="&#100;ata:image/png;base64,'.$png.'"></p>'])
            ->assertSessionHasErrors(['question_text' => 'Gambar harus diunggah lewat tombol gambar di editor, bukan ditempel langsung.']);

        $this->assertSame(0, Question::count());
    }

    public function test_text_over_the_column_byte_limit_is_rejected_even_under_the_character_limit(): void
    {
        $long = '<p>'.str_repeat("\u{20AC}", 22000).'</p>'; // 66000 bytes, 22000 characters

        $this->store(['question_text' => $long])
            ->assertSessionHasErrors(['question_text' => 'Teks terlalu panjang. Kurangi isi atau pecah menjadi beberapa soal.']);
        $this->store(['explanation' => $long])->assertSessionHasErrors('explanation');
        $this->store(['options' => [
            ['text' => $long, 'is_correct' => 1],
            ['text' => '<p>B</p>', 'is_correct' => 0],
        ]])->assertSessionHasErrors(['options.0.text' => 'Teks pilihan jawaban terlalu panjang.']);
        $this->store([
            'question_type' => 'drag_and_drop',
            'question_text' => '<p>[[BLANK_1]]</p>',
            'options' => [['text' => str_repeat("\u{20AC}", 22000), 'gap_id' => 'BLANK_1'], ['text' => 'Roma', 'gap_id' => '']],
        ])->assertSessionHasErrors('options.0.text');

        $this->assertSame(0, Question::count());
    }

    public function test_question_list_loads_its_script_after_jquery(): void
    {
        $html = $this->asInstructor()->get(route('instructor.question-bank.questions.index', $this->topic))
            ->assertOk()->getContent();

        $jquery = strpos($html, 'jquery.min.js');
        $moveScript = strpos($html, 'let availableTopics');

        $this->assertNotFalse($jquery);
        $this->assertNotFalse($moveScript);
        $this->assertGreaterThan($jquery, $moveScript);
    }

    public function test_create_forms_restore_options_after_a_validation_error(): void
    {
        $createUrl = route('instructor.question-bank.questions.create', [$this->topic, 'type' => 'multiple_choice_single']);

        $this->asInstructor()->from($createUrl)->post(route('instructor.question-bank.questions.store', $this->topic), [
            'question_type' => 'multiple_choice_single',
            'question_text' => '<p>&nbsp;</p>',
            'score' => 10,
            'options' => [
                ['text' => '<p>Pilihan A</p>', 'is_correct' => 0],
                ['text' => '<p>Pilihan B</p>', 'is_correct' => 1],
                ['text' => '<p>Pilihan C</p>', 'is_correct' => 0],
            ],
        ])->assertRedirect($createUrl);

        $html = $this->asInstructor()->get($createUrl)->assertOk()
            ->assertSee('const oldOptions = ', false)
            ->assertSee('Pilihan B', false)
            ->getContent();

        $this->assertMatchesRegularExpression('/Pilihan B.{0,20}"is_correct":"?1"?/', $html);
    }
}

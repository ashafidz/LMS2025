<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionTopic;
use App\Support\HtmlSanitizer;
use App\Support\ListQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    // A MySQL TEXT column holds 65535 bytes; leave room for what the sanitizer adds.
    private const MAX_COLUMN_BYTES = 65000;

    private const TYPES = ['multiple_choice_single', 'multiple_choice_multiple', 'true_false', 'drag_and_drop'];

    public function index(QuestionTopic $topic)
    {
        abort_unless($topic->instructor_id === Auth::id(), 403);

        $questions = ListQuery::applySearch($topic->questions()->with('options', 'quizzes'), ['question_text'])
            ->latest()->paginate(ListQuery::perPage())->withQueryString();

        // Load topic with courses for modal info
        $topic->load('courses');

        // Get all questions for breakdown statistics (not paginated)
        $allQuestions = $topic->questions()->get();

        // Get all available topics for the move functionality
        $user = Auth::user();
        $availableTopics = QuestionTopic::where('instructor_id', $user->id)
            ->with('courses:id,title')
            ->withCount('questions')
            ->where('id', '!=', $topic->id) // Exclude current topic
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'description' => $t->description,
                    'available_for_all_courses' => $t->available_for_all_courses,
                    'questions_count' => $t->questions_count,
                    'courses' => $t->courses->map(function ($course) {
                        return ['id' => $course->id, 'title' => $course->title];
                    }),
                ];
            });

        // Get courses for filter dropdown
        $courses = $user->courses()->orderBy('title')->get(['id', 'title']);

        return view('instructor.question_bank.questions.index', compact('topic', 'questions', 'allQuestions', 'availableTopics', 'courses'));
    }

    public function create(Request $request, QuestionTopic $topic)
    {
        abort_unless($topic->instructor_id === Auth::id(), 403);

        $questionType = $request->query('type');
        $validTypes = ['multiple_choice_single', 'multiple_choice_multiple', 'true_false', 'drag_and_drop'];

        if (! in_array($questionType, $validTypes)) {
            abort(404, 'Invalid question type specified.');
        }

        $viewName = "instructor.question_bank.questions.create-{$questionType}";

        return view($viewName, compact('topic'));
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request, QuestionTopic $topic)
    {
        abort_unless($topic->instructor_id === Auth::id(), 403);

        $validated = $this->validateQuestion($request, $request->input('question_type'), [
            'question_type' => ['required', Rule::in(self::TYPES)],
        ]);

        DB::transaction(function () use ($validated, $topic) {
            $question = $topic->questions()->create([
                'question_text' => HtmlSanitizer::clean($validated['question_text']),
                'score' => $validated['score'],
                'explanation' => $this->cleanExplanation($validated['explanation'] ?? null),
                'question_type' => $validated['question_type'],
                'text_format' => Question::FORMAT_HTML,
            ]);

            foreach ($validated['options'] as $optionData) {
                // LOGIKA BARU YANG DISEMPURNAKAN
                $isCorrect = $optionData['is_correct'] ?? false;
                if ($validated['question_type'] === 'drag_and_drop') {
                    // Jika ini soal drag and drop, is_correct ditentukan oleh keberadaan gap_id
                    $isCorrect = ! empty($optionData['gap_id']);
                }

                $question->options()->create([
                    'option_text' => $this->hasRichOptions($validated['question_type']) ? HtmlSanitizer::clean($optionData['text']) : $optionData['text'],
                    'is_correct' => $isCorrect,
                    'correct_gap_identifier' => $validated['question_type'] === 'drag_and_drop' ? ($optionData['gap_id'] ?? null) : null,
                ]);
            }
        });

        return redirect()->route('instructor.question-bank.questions.index', $topic)
            ->with('success', 'Soal berhasil dibuat.');
    }

    public function edit(Question $question)
    {
        abort_unless($question->topic && $question->topic->instructor_id === Auth::id(), 403);

        if ($question->quizzes()->exists()) {
            return redirect()->route('instructor.question-bank.questions.index', $question->topic)
                ->with('error', 'Soal ini terkunci karena sudah dipakai di kuis. Salin soal ini untuk mengubahnya.');
        }
        $questionType = $question->question_type;
        $viewName = "instructor.question_bank.questions.edit-{$questionType}";
        $question->load('options');

        return view($viewName, compact('question'));
    }

    /**
     * Update the specified question in storage.
     */
    public function update(Request $request, Question $question)
    {
        abort_unless($question->topic && $question->topic->instructor_id === Auth::id(), 403);

        if ($question->quizzes()->exists()) {
            abort(403, 'Soal ini terkunci karena sudah dipakai di kuis.');
        }

        $validated = $this->validateQuestion($request, $question->question_type);

        DB::transaction(function () use ($validated, $question) {
            $question->update([
                'question_text' => HtmlSanitizer::clean($validated['question_text']),
                'score' => $validated['score'],
                'explanation' => $this->cleanExplanation($validated['explanation'] ?? null),
                'text_format' => Question::FORMAT_HTML,
            ]);

            $question->options()->delete();

            foreach ($validated['options'] as $optionData) {
                // LOGIKA BARU YANG DISEMPURNAKAN
                $isCorrect = $optionData['is_correct'] ?? false;
                if ($question->question_type === 'drag_and_drop') {
                    $isCorrect = ! empty($optionData['gap_id']);
                }

                $question->options()->create([
                    'option_text' => $this->hasRichOptions($question->question_type) ? HtmlSanitizer::clean($optionData['text']) : $optionData['text'],
                    'is_correct' => $isCorrect,
                    'correct_gap_identifier' => $question->question_type === 'drag_and_drop' ? ($optionData['gap_id'] ?? null) : null,
                ]);
            }
        });

        return redirect()->route('instructor.question-bank.questions.index', $question->topic)
            ->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(Question $question)
    {
        abort_unless($question->topic && $question->topic->instructor_id === Auth::id(), 403);

        $topic = $question->topic;
        $question->delete();

        return redirect()->route('instructor.question-bank.questions.index', $topic)
            ->with('success', 'Soal dipindahkan ke tempat sampah.');
    }

    public function clone(Question $question)
    {
        abort_unless($question->topic && $question->topic->instructor_id === Auth::id(), 403);

        $newQuestion = DB::transaction(function () use ($question) {
            $newQuestion = $question->replicate();
            $newQuestion->question_text = $question->question_text.($question->isHtml() ? '<p>(Copy)</p>' : ' (Copy)');
            $newQuestion->push();
            foreach ($question->options as $option) {
                $newOption = $option->replicate();
                $newQuestion->options()->save($newOption);
            }

            return $newQuestion;
        });

        return redirect()->route('instructor.question-bank.questions.edit', $newQuestion)
            ->with('success', 'Soal berhasil disalin. Anda sekarang mengedit salinannya.');
    }

    /**
     * Move question to another topic
     */
    public function move(Request $request, Question $question)
    {
        $validated = $request->validate([
            'target_topic_id' => 'required|exists:question_topics,id',
        ]);

        $user = Auth::user();
        $targetTopic = QuestionTopic::findOrFail($validated['target_topic_id']);

        // Ensure the target topic belongs to the current user
        if ($targetTopic->instructor_id != $user->id) {
            abort(403, 'Anda tidak berhak memindahkan soal ke topik ini.');
        }

        // Ensure the question belongs to the current user
        if ($question->topic->instructor_id != $user->id) {
            abort(403, 'Anda tidak berhak memindahkan soal ini.');
        }

        // Check if question is locked (used in quizzes)
        if ($question->quizzes()->exists()) {
            return redirect()->back()
                ->with('error', 'Soal yang terkunci tidak bisa dipindahkan karena sedang dipakai di satu atau lebih kuis.');
        }

        $oldTopicName = $question->topic->name;
        $currentTopic = $question->topic;

        // Move the question using the correct foreign key column name
        $question->update(['topic_id' => $validated['target_topic_id']]);

        return redirect()->route('instructor.question-bank.questions.index', $currentTopic)
            ->with('success', "Soal berhasil dipindahkan dari '{$oldTopicName}' ke '{$targetTopic->name}'.");
    }

    /**
     * API endpoint to get filtered topics for the move modal
     */
    public function getFilteredTopics(Request $request)
    {
        $user = Auth::user();
        $filter = $request->query('filter');
        $currentTopicId = $request->query('current_topic_id');

        $query = $user->questionTopics()
            ->with('courses:id,title')
            ->withCount('questions')
            ->where('id', '!=', $currentTopicId);

        // Apply the same filtering logic as in the topics index
        if ($filter === 'global') {
            $query->where('available_for_all_courses', true);
        } elseif (is_numeric($filter)) {
            $query->where(function ($q) use ($filter) {
                $q->where('available_for_all_courses', true)
                    ->orWhereHas('courses', function ($subQ) use ($filter) {
                        $subQ->where('course_id', $filter);
                    });
            });
        }

        $topics = $query->latest()->get()->map(function ($topic) {
            return [
                'id' => $topic->id,
                'name' => $topic->name,
                'description' => $topic->description,
                'available_for_all_courses' => $topic->available_for_all_courses,
                'questions_count' => $topic->questions_count,
                'courses' => $topic->courses->map(function ($course) {
                    return ['id' => $course->id, 'title' => $course->title];
                }),
            ];
        });

        return response()->json($topics);
    }

    private function hasRichOptions(?string $type): bool
    {
        return in_array($type, ['multiple_choice_single', 'multiple_choice_multiple'], true);
    }

    /**
     * Shared validation for store and update. $type is the question type (from the request on store).
     */
    private function validateQuestion(Request $request, ?string $type, array $extraRules = []): array
    {
        $validator = Validator::make($request->all(), $extraRules + [
            'question_text' => 'required|string|max:60000',
            'score' => 'required|integer|min:1',
            'explanation' => 'nullable|string|max:60000',
            'options' => 'required|array|min:2',
            'options.*.text' => 'required|string|max:60000',
            'options.*.is_correct' => 'nullable|boolean',
            'options.*.gap_id' => 'nullable|string',
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa angka bulat.',
            'array' => ':attribute tidak valid.',
            'boolean' => ':attribute tidak valid.',
            // Size rules (max, min) need one message per value type.
            'max' => ['string' => ':attribute terlalu panjang (maksimal :max karakter).'],
            'min' => ['numeric' => ':attribute minimal :min.', 'array' => 'Isi minimal :min :attribute.'],
        ], [
            'question_text' => 'Teks pertanyaan',
            'explanation' => 'Penjelasan',
            'score' => 'Nilai',
            'options' => 'Pilihan jawaban',
            'options.*.text' => 'Teks pilihan jawaban',
            'options.*.gap_id' => 'ID kosong',
        ]);

        $validator->after(function ($validator) use ($request, $type) {
            $errors = $validator->errors();
            $text = (string) $request->input('question_text');
            $explanation = (string) $request->input('explanation');
            $options = (array) $request->input('options', []);
            $dataImageMessage = 'Gambar harus diunggah lewat tombol gambar di editor, bukan ditempel langsung.';

            $this->checkRichValue($errors, 'question_text', $text, 'Teks terlalu panjang. Kurangi isi atau pecah menjadi beberapa soal.', $dataImageMessage);
            $this->checkRichValue($errors, 'explanation', $explanation, 'Teks terlalu panjang. Kurangi isi atau pecah menjadi beberapa soal.', $dataImageMessage);
            if ($this->isEmptyRich($text) && ! $errors->has('question_text')) {
                $errors->add('question_text', 'Teks pertanyaan tidak boleh kosong.');
            }

            foreach ($options as $i => $option) {
                $optionText = (string) ($option['text'] ?? '');
                if ($this->hasRichOptions($type)) {
                    $this->checkRichValue($errors, "options.$i.text", $optionText, 'Teks pilihan jawaban terlalu panjang.', $dataImageMessage);
                    if ($this->isEmptyRich($optionText) && ! $errors->has("options.$i.text")) {
                        $errors->add("options.$i.text", 'Setiap pilihan jawaban harus diisi.');
                    }
                } elseif (strlen($optionText) > self::MAX_COLUMN_BYTES && ! $errors->has("options.$i.text")) {
                    $errors->add("options.$i.text", 'Teks pilihan jawaban terlalu panjang.');
                }
            }

            if ($type === 'drag_and_drop') {
                $this->validateBlanks($validator, $text, $options);
            }
        });

        return $validator->validate();
    }

    private function validateBlanks($validator, string $text, array $options): void
    {
        $message = 'Penanda :gap tidak ditemukan utuh di teks pertanyaan. Pastikan tulisannya persis [[BLANK_1]] tanpa format tebal/miring di tengahnya.';
        preg_match_all('/\[\[(BLANK_\d+)\]\]/', strip_tags($text), $matches);
        $blankIds = array_unique($matches[1]);

        foreach ($options as $i => $option) {
            $gap = $option['gap_id'] ?? null;
            if (! empty($gap) && ! in_array($gap, $blankIds, true)) {
                $validator->errors()->add("options.$i.gap_id", str_replace(':gap', $gap, $message));
            }
        }

        foreach ($blankIds as $id) {
            if (! str_contains($text, "[[$id]]")) {
                $validator->errors()->add('question_text', str_replace(':gap', $id, $message));
            }
        }
    }

    /**
     * Rejects pasted base64 images and values that would not fit a TEXT column once sanitized.
     * The data image check also runs on the sanitized HTML, because HTMLPurifier decodes entities like &#100;ata:.
     */
    private function checkRichValue($errors, string $key, string $raw, string $tooLongMessage, string $dataImageMessage): void
    {
        if ($errors->has($key)) {
            return;
        }

        $clean = HtmlSanitizer::clean($raw);

        if ($this->hasDataImage($raw) || $this->hasDataImage($clean)) {
            $errors->add($key, $dataImageMessage);

            return;
        }

        if (strlen($clean) > self::MAX_COLUMN_BYTES) {
            $errors->add($key, $tooLongMessage);
        }
    }

    private function hasDataImage(string $html): bool
    {
        return (bool) preg_match('/src\s*=\s*["\']?\s*data:/i', $html);
    }

    /**
     * Empty when there is no visible text and no image (for example "<p>&nbsp;</p>").
     */
    private function isEmptyRich(string $html): bool
    {
        $text = trim(str_replace("\xC2\xA0", ' ', html_entity_decode(strip_tags($html), ENT_QUOTES)));

        return $text === '' && stripos($html, '<img') === false;
    }

    private function cleanExplanation(?string $explanation): ?string
    {
        if ($explanation === null || $this->isEmptyRich($explanation)) {
            return null;
        }

        return HtmlSanitizer::clean($explanation);
    }
}

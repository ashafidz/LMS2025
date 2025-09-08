<?php

namespace App\Http\Controllers\Instructor;

use App\Models\Question;
use App\Models\QuestionTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class QuestionController extends Controller
{
    public function index(QuestionTopic $topic)
    {
        $questions = $topic->questions()->with('options', 'quizzes')->latest()->simplePaginate(10);
        return view('instructor.question_bank.questions.index', compact('topic', 'questions'));
    }

    public function create(Request $request, QuestionTopic $topic)
    {
        $questionType = $request->query('type');
        $validTypes = ['multiple_choice_single', 'multiple_choice_multiple', 'true_false', 'drag_and_drop'];

        if (!in_array($questionType, $validTypes)) {
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
        $validated = $request->validate([
            'question_text' => 'required|string',
            'score' => 'required|integer|min:1',
            'explanation' => 'nullable|string',
            'question_type' => ['required', Rule::in(['multiple_choice_single', 'multiple_choice_multiple', 'true_false', 'drag_and_drop'])],
            'options' => 'required|array|min:2',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'nullable|boolean',
            'options.*.gap_id' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $topic) {
            $question = $topic->questions()->create([
                'question_text' => $validated['question_text'],
                'score' => $validated['score'],
                'explanation' => $validated['explanation'],
                'question_type' => $validated['question_type'],
            ]);

            foreach ($validated['options'] as $optionData) {
                // LOGIKA BARU YANG DISEMPURNAKAN
                $isCorrect = $optionData['is_correct'] ?? false;
                if ($validated['question_type'] === 'drag_and_drop') {
                    // Jika ini soal drag and drop, is_correct ditentukan oleh keberadaan gap_id
                    $isCorrect = !empty($optionData['gap_id']);
                }

                $question->options()->create([
                    'option_text' => $optionData['text'],
                    'is_correct' => $isCorrect,
                    'correct_gap_identifier' => $validated['question_type'] === 'drag_and_drop' ? ($optionData['gap_id'] ?? null) : null,
                ]);
            }
        });

        return redirect()->route('instructor.question-bank.questions.index', $topic)
            ->with('success', 'Question created successfully.');
    }

    public function edit(Question $question)
    {
        if ($question->quizzes()->exists()) {
            return redirect()->route('instructor.question-bank.questions.index', $question->topic)
                ->with('error', 'This question is locked and cannot be edited. Please clone it instead.');
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
        if ($question->quizzes()->exists()) {
            abort(403, 'This question is locked and cannot be edited.');
        }

        $validated = $request->validate([
            'question_text' => 'required|string',
            'score' => 'required|integer|min:1',
            'explanation' => 'nullable|string',
            'options' => 'required|array|min:2',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'nullable|boolean',
            'options.*.gap_id' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $question) {
            $question->update([
                'question_text' => $validated['question_text'],
                'score' => $validated['score'],
                'explanation' => $validated['explanation'],
            ]);

            $question->options()->delete();

            foreach ($validated['options'] as $optionData) {
                // LOGIKA BARU YANG DISEMPURNAKAN
                $isCorrect = $optionData['is_correct'] ?? false;
                if ($question->question_type === 'drag_and_drop') {
                    $isCorrect = !empty($optionData['gap_id']);
                }

                $question->options()->create([
                    'option_text' => $optionData['text'],
                    'is_correct' => $isCorrect,
                    'correct_gap_identifier' => $question->question_type === 'drag_and_drop' ? ($optionData['gap_id'] ?? null) : null,
                ]);
            }
        });

        return redirect()->route('instructor.question-bank.questions.index', $question->topic)
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(Question $question)
    {
        $topic = $question->topic;
        $question->delete();
        return redirect()->route('instructor.question-bank.questions.index', $topic)
            ->with('success', 'Question moved to trash.');
    }

    public function clone(Question $question)
    {
        $newQuestion = DB::transaction(function () use ($question) {
            $newQuestion = $question->replicate();
            $newQuestion->question_text = $question->question_text . ' (Copy)';
            $newQuestion->push();
            foreach ($question->options as $option) {
                $newOption = $option->replicate();
                $newQuestion->options()->save($newOption);
            }
            return $newQuestion;
        });
        return redirect()->route('instructor.question-bank.questions.edit', $newQuestion)
            ->with('success', 'Question cloned successfully. You are now editing the copy.');
    }
}

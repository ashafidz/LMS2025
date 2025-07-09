<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\QuestionTopic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($topicId)
    {
        $topic = \App\Models\QuestionTopic::find($topicId); // Manually load

        // Ownership check
        if (!$topic || $topic->instructor_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $questions = $topic->questions()
            ->withCount('quizzes') // Eager load the count of quizzes
            ->with('options')
            ->latest()
            ->paginate(10);
        // count the question and insert it in questions variable as
        return view('instructor.question_bank.questions.index', compact('topic', 'questions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($topicId)
    {
        $topic = \App\Models\QuestionTopic::find($topicId); // Manually load

        // Ownership check
        if (!$topic || $topic->instructor_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('instructor.question_bank.questions.create', compact('topic'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $topicId)
    {
        $topic = \App\Models\QuestionTopic::find($topicId); // Manually load

        // Ownership check
        if (!$topic || $topic->instructor_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'question_text' => 'required|string',
            'score' => 'required|integer|min:1',
            'question_type' => ['required', Rule::in(['multiple_choice_single', 'multiple_choice_multiple', 'true_false', 'drag_and_drop'])],
            // Add validation rules for options based on type
            'options' => 'required|array|min:2',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'nullable|boolean',
            'options.*.gap_id' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $topic) {
            // 1. Create the Question
            $question = $topic->questions()->create([
                'question_text' => $validated['question_text'],
                'score' => $validated['score'],
                'question_type' => $validated['question_type'],
            ]);

            // 2. Create the Options
            foreach ($validated['options'] as $optionData) {
                $question->options()->create([
                    'option_text' => $optionData['text'],
                    'is_correct' => $optionData['is_correct'] ?? false,
                    'correct_gap_identifier' => $validated['question_type'] === 'drag_and_drop' ? $optionData['gap_id'] : null,
                ]);
            }
        });

        return redirect()->route('instructor.question-bank.questions.index', $topic)
            ->with('success', 'Question created successfully.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($questionId)
    {
        $question = \App\Models\Question::find($questionId); // Manually load

        // Ownership check
        if (!$question || $question->topic->instructor_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $question->load('options', 'topic'); // Eager load relationships
        return view('instructor.question_bank.questions.edit', compact('question'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $questionId)
    {
        $question = \App\Models\Question::find($questionId); // Manually load

        // Ownership check
        if (!$question || $question->topic->instructor_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'question_text' => 'required|string',
            'score' => 'required|integer|min:1',
            // Can't change question type after creation to avoid data inconsistency
            'options' => 'required|array|min:2',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'nullable|boolean',
            'options.*.gap_id' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $question) {
            // 1. Update the Question
            $question->update([
                'question_text' => $validated['question_text'],
                'score' => $validated['score'],
            ]);

            // 2. Delete old options and create new ones (simplest approach)
            $question->options()->delete();

            foreach ($validated['options'] as $optionData) {
                $question->options()->create([
                    'option_text' => $optionData['text'],
                    'is_correct' => $optionData['is_correct'] ?? false,
                    'correct_gap_identifier' => $question->question_type === 'drag_and_drop' ? $optionData['gap_id'] : null,
                ]);
            }
        });

        return redirect()->route('instructor.question-bank.questions.index', $question->topic)
            ->with('success', 'Question updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($questionId)
    {
        $question = \App\Models\Question::find($questionId); // Manually load

        // Ownership check
        if (!$question || $question->topic->instructor_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $topic = $question->topic;
        $question->delete(); // This triggers the soft delete and the 'deleting' event in the model

        return redirect()->route('instructor.question-bank.questions.index', $topic)
            ->with('success', 'Question moved to trash.');
    }

    /**
     * Clone a question and its options.
     */
    public function clone($questionId)
    {
        $question = \App\Models\Question::find($questionId); // Manually load

        // Ownership check
        if (!$question || $question->topic->instructor_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $newQuestion = \Illuminate\Support\Facades\DB::transaction(function () use ($question) {
            // 1. Replicate the question to create a new, unsaved instance
            $newQuestion = $question->replicate();
            $newQuestion->question_text = $question->question_text . ' (Copy)';
            $newQuestion->push(); // push() saves the model and its relationships

            // 2. Replicate each option and associate it with the new question
            foreach ($question->options as $option) {
                $newOption = $option->replicate();
                $newQuestion->options()->save($newOption);
            }

            return $newQuestion;
        });

        // Redirect the instructor to the edit page for the newly created question
        return redirect()->route('instructor.question-bank.questions.edit', $newQuestion)
            ->with('success', 'Question cloned successfully. You are now editing the copy.');
    }
}

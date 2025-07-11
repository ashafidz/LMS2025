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
    /**
     * Display a listing of the questions for a given topic.
     */
    public function index(QuestionTopic $topic)
    {
        // Manual Authorization Check
        if (Auth::id() !== $topic->instructor_id) {
            abort(403, 'Unauthorized action.');
        }

        // Eager load relationships to check lock status and show options in the view
        $questions = $topic->questions()->with('options', 'quizzes')->latest()->paginate(10);

        return view('instructor.question_bank.questions.index', compact('topic', 'questions'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create(Request $request, QuestionTopic $topic)
    {
        // Manual Authorization Check
        if (Auth::id() !== $topic->instructor_id) {
            abort(403, 'Unauthorized action.');
        }

        // Get the requested question type from the URL query string
        $questionType = $request->query('type');

        // Define the valid types and their corresponding view files
        $validTypes = [
            'multiple_choice_single',
            'multiple_choice_multiple',
            'true_false',
            'drag_and_drop'
        ];

        // Check if the requested type is valid
        if (!in_array($questionType, $validTypes)) {
            // Or redirect back to the modal/index page with an error
            abort(404, 'Invalid question type specified.');
        }

        // Construct the view name dynamically and pass the topic
        $viewName = "instructor.question_bank.questions.create-{$questionType}";

        return view($viewName, compact('topic'));
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request, QuestionTopic $topic)
    {
        // Manual Authorization Check
        if (Auth::id() !== $topic->instructor_id) {
            abort(403, 'Unauthorized action.');
        }

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
            // Create the Question
            $question = $topic->questions()->create([
                'question_text' => $validated['question_text'],
                'score' => $validated['score'],
                'explanation' => $validated['explanation'],
                'question_type' => $validated['question_type'],
            ]);

            // Create the Options based on the validated data
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
     * Show the correct edit form based on the question's type.
     */
    public function edit(Question $question)
    {
        // Manual Authorization Check
        if (Auth::id() !== $question->topic->instructor_id) {
            abort(403, 'Unauthorized action.');
        }

        // Server-side lock check. Redirect with an error if the question is in use.
        // This is a safeguard in case the 'Edit' button was somehow visible on a locked question.
        if ($question->quizzes()->exists()) {
            return redirect()->route('instructor.question-bank.questions.index', $question->topic)
                ->with('error', 'This question is locked and cannot be edited. Please clone it instead.');
        }

        // Get the question type directly from the model
        $questionType = $question->question_type;

        // Construct the view name dynamically based on the type
        $viewName = "instructor.question_bank.questions.edit-{$questionType}";

        // Eager load the relationships needed for the view
        $question->load('options');

        // Return the specific view for that question type
        return view($viewName, compact('question'));
    }

    /**
     * Update the specified question in storage.
     */
    public function update(Request $request, Question $question)
    {
        // Manual Authorization & Lock Check
        if (Auth::id() !== $question->topic->instructor_id) {
            abort(403, 'Unauthorized action.');
        }
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
            // Update the Question details
            $question->update([
                'question_text' => $validated['question_text'],
                'score' => $validated['score'],
                'explanation' => $validated['explanation'],
            ]);

            // Soft-delete old options and recreate them
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
     * Remove the specified question from storage (soft delete).
     */
    public function destroy(Question $question)
    {
        // Manual Authorization Check
        if (Auth::id() !== $question->topic->instructor_id) {
            abort(403, 'Unauthorized action.');
        }

        $topic = $question->topic;
        $question->delete();

        return redirect()->route('instructor.question-bank.questions.index', $topic)
            ->with('success', 'Question moved to trash.');
    }

    /**
     * Clone a locked question and its options.
     */
    public function clone(Question $question)
    {
        // Manual Authorization Check (user must own the question to clone it)
        if (Auth::id() !== $question->topic->instructor_id) {
            abort(403, 'Unauthorized action.');
        }

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

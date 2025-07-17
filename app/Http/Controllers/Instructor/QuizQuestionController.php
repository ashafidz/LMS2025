<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizQuestionController extends Controller
{
    public function index(Quiz $quiz)
    {
        // Otorisasi dihapus
        $attachedQuestions = $quiz->questions()->orderBy('pivot_order')->get();
        return view('instructor.quizzes.manage-questions', compact('quiz', 'attachedQuestions'));
    }

    public function browseBank(Request $request, Quiz $quiz)
    {
        // Otorisasi dihapus
        $existingQuestionIds = $quiz->questions()->pluck('questions.id');
        $topics = Auth::user()->questionTopics()->withCount(['questions' => function ($query) use ($existingQuestionIds) {
            $query->whereNotIn('id', $existingQuestionIds);
        }])->get();

        $questionsInTopic = null;
        if ($request->has('topic_id')) {
            $selectedTopic = QuestionTopic::findOrFail($request->topic_id);
            // Otorisasi topik dihapus
            $questionsInTopic = $selectedTopic->questions()
                ->whereNotIn('id', $existingQuestionIds)
                ->get();
        }
        return view('instructor.quizzes.browse-bank', compact('quiz', 'topics', 'questionsInTopic'));
    }

    public function attachQuestions(Request $request, Quiz $quiz)
    {
        // Otorisasi dihapus
        $validated = $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id',
        ]);
        $quiz->questions()->syncWithoutDetaching($validated['question_ids']);
        return redirect()->route('instructor.quizzes.manage_questions', $quiz)->with('success', 'Soal berhasil ditambahkan ke kuis.');
    }

    public function detachQuestion(Quiz $quiz, Question $question)
    {
        // Otorisasi dihapus
        $quiz->questions()->detach($question->id);
        return back()->with('success', 'Soal berhasil dihapus dari kuis.');
    }
}

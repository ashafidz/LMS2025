<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\QuestionTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionTopicController extends Controller
{
    public function index()
    {
        $topics = QuestionTopic::where('instructor_id', Auth::id())
            ->withExists([
                'questions as is_locked' => function ($query) {
                    $query->whereHas('quizzes');
                }
            ])
            ->latest()
            ->paginate(10);
        foreach ($topics as $topic) {
            $topic->questions_count = $topic->questions()->count();
        }
        return view('instructor.question_bank.topics.index', compact('topics'));
    }

    public function create()
    {
        return view('instructor.question_bank.topics.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        Auth::user()->questionTopics()->create($validated);
        return redirect()->route('instructor.question-bank.topics.index')
            ->with('success', 'Question topic created successfully.');
    }

    public function show($id)
    {
        $topic = QuestionTopic::find($id);
        // Otorisasi dihapus
        return redirect()->route('instructor.question-bank.questions.index', $topic);
    }

    public function edit($id)
    {
        $topic = QuestionTopic::find($id);
        // Otorisasi dihapus
        return view('instructor.question_bank.topics.edit', compact('topic'));
    }

    public function update(Request $request, $id)
    {
        $topic = QuestionTopic::find($id);
        // Otorisasi dihapus
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $topic->update($validated);
        return redirect()->route('instructor.question-bank.topics.index')
            ->with('success', 'Question topic updated successfully.');
    }

    public function destroy($id)
    {
        $topic = \App\Models\QuestionTopic::find($id);
        // Otorisasi dihapus
        if ($topic) {
            $topic->delete();
            return redirect()->route('instructor.question-bank.topics.index')
                ->with('success', 'Question topic deleted successfully.');
        }
        return redirect()->route('instructor.question-bank.topics.index')
            ->with('error', 'Question topic not found.');
    }
}

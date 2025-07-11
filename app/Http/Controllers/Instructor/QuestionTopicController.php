<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\QuestionTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionTopicController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     // Get topics created only by the currently logged-in instructor
    //     $topics = QuestionTopic::where('instructor_id', Auth::id())->latest()->paginate(10);
    //     return view('instructor.question_bank.topics.index', compact('topics'));
    // }
    // public function index()
    // {
    //     // Get topics and check if each has questions linked to quizzes.
    //     // 'withExists' is very efficient for this check.
    //     $topics = QuestionTopic::where('instructor_id', Auth::id())
    //         ->withExists(['questions' => function ($query) {
    //             $query->whereHas('quizzes');
    //         }], 'is_locked') // The result will be on a 'is_locked' property
    //         ->latest()
    //         ->paginate(10);

    //     return view('instructor.question_bank.topics.index', compact('topics'));
    // }

    public function index()
    {
        // Get topics and check if each has questions linked to quizzes.
        $topics = QuestionTopic::where('instructor_id', Auth::id())
            ->withExists([
                // CORRECT SYNTAX: The alias 'is_locked' is defined with 'as' in the key.
                'questions as is_locked' => function ($query) {
                    $query->whereHas('quizzes');
                }
            ])
            ->latest()
            ->paginate(10);
        // count the question in topic and insert it in topics variable as questions_count
        foreach ($topics as $topic) {
            $topic->questions_count = $topic->questions()->count();
        }

        return view('instructor.question_bank.topics.index', compact('topics'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('instructor.question_bank.topics.create');
    }

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Display the specified resource.
     * We redirect to the questions index for that topic.
     */
    public function show($id) // Changed signature
    {
        $topic = QuestionTopic::find($id); // Manually load

        // Ownership check
        if (!$topic || $topic->instructor_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Redirect to the list of questions for this topic
        return redirect()->route('instructor.question-bank.questions.index', $topic);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id) // Changed signature
    {
        $topic = QuestionTopic::find($id); // Manually load

        // Ownership check
        if (!$topic || $topic->instructor_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('instructor.question_bank.topics.edit', compact('topic'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) // Changed signature
    {
        $topic = QuestionTopic::find($id); // Manually load

        // Ownership check
        if (!$topic || $topic->instructor_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $topic->update($validated);

        return redirect()->route('instructor.question-bank.topics.index')
            ->with('success', 'Question topic updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) // Changed signature
    {
        $topic = \App\Models\QuestionTopic::find($id); // Manually load

        if ($topic) {
            $topic->delete();
            return redirect()->route('instructor.question-bank.topics.index')
                ->with('success', 'Question topic deleted successfully.');
        }

        return redirect()->route('instructor.question-bank.topics.index')
            ->with('error', 'Question topic not found.');
    }
}

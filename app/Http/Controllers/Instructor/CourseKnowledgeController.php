<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseKnowledgeQuestion;
use App\Models\CourseKnowledgeOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseKnowledgeController extends Controller
{
    /**
     * Check if instructor owns the course
     */
    private function checkAccess(Course $course)
    {
        if ($course->instructor_id != Auth::id()) {
            abort(403, 'Akses ditolak.');
        }
    }

    public function index(Course $course)
    {
        $this->checkAccess($course);
        
        if (!$course->isAdaptive()) {
            return redirect()->route('instructor.courses.index')->with('error', 'Kursus ini bukan tipe Adaptive.');
        }

        $course->load(['knowledgeQuestions.options' => function($q) {
            $q->orderBy('order');
        }]);

        return view('instructor.knowledge-questions.index', compact('course'));
    }

    public function storeQuestion(Request $request, Course $course)
    {
        $this->checkAccess($course);

        $validated = $request->validate([
            'question_text' => 'required|string',
            'order' => 'required|integer',
        ]);

        $course->knowledgeQuestions()->create($validated);
        return back()->with('success', 'Soal berhasil ditambahkan.');
    }

    public function updateQuestion(Request $request, CourseKnowledgeQuestion $question)
    {
        $this->checkAccess($question->course);

        $validated = $request->validate([
            'question_text' => 'required|string',
            'order' => 'required|integer',
        ]);

        $question->update($validated);
        return back()->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroyQuestion(CourseKnowledgeQuestion $question)
    {
        $this->checkAccess($question->course);
        $question->delete();
        return back()->with('success', 'Soal berhasil dihapus.');
    }

    public function storeOption(Request $request, CourseKnowledgeQuestion $question)
    {
        $this->checkAccess($question->course);

        $validated = $request->validate([
            'option_text' => 'required|string',
            'order' => 'required|integer',
            'is_correct' => 'nullable|boolean',
        ]);

        // If this is set as correct, and it's single choice, we might want to un-set others
        // But for now, just save it.
        $question->options()->create([
            'option_text' => $validated['option_text'],
            'order' => $validated['order'],
            'is_correct' => $request->has('is_correct'),
        ]);

        return back()->with('success', 'Opsi berhasil ditambahkan.');
    }

    public function updateOption(Request $request, CourseKnowledgeOption $option)
    {
        $this->checkAccess($option->question->course);

        $validated = $request->validate([
            'option_text' => 'required|string',
            'order' => 'required|integer',
            'is_correct' => 'nullable|boolean',
        ]);

        $option->update([
            'option_text' => $validated['option_text'],
            'order' => $validated['order'],
            'is_correct' => $request->has('is_correct'),
        ]);

        return back()->with('success', 'Opsi berhasil diperbarui.');
    }

    public function destroyOption(CourseKnowledgeOption $option)
    {
        $this->checkAccess($option->question->course);
        $option->delete();
        return back()->with('success', 'Opsi berhasil dihapus.');
    }
}

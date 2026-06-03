<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ProfilingAttempt;
use App\Models\ProfilingComponent;
use App\Models\ProfilingLikertAnswer;
use App\Models\ProfilingMcqAnswer;
use App\Services\ProfilingScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfilingTestController extends Controller
{
    private ProfilingScoreService $scoreService;

    public function __construct(ProfilingScoreService $scoreService)
    {
        $this->scoreService = $scoreService;
    }

    private function checkAccess(Course $course)
    {
        $user = Auth::user();
        if (!$course->isAdaptive()) {
            abort(404, 'Kursus ini bukan tipe Adaptive.');
        }

        $is_enrolled = $user->enrollments()->where('course_id', $course->id)->exists();
        if (!$is_enrolled) {
            abort(403, 'Anda belum terdaftar di kursus ini.');
        }

        $attempt = ProfilingAttempt::firstOrCreate(
            ['student_id' => $user->id, 'course_id' => $course->id],
            ['status' => 'in_progress', 'current_component' => 1]
        );

        if ($attempt->status === 'completed') {
            return ['completed' => true, 'attempt' => $attempt];
        }

        return ['completed' => false, 'attempt' => $attempt];
    }

    public function intro(Course $course)
    {
        $access = $this->checkAccess($course);
        if ($access['completed']) {
            return redirect()->route('student.courses.show', $course->slug);
        }

        $attempt = $access['attempt'];
        if ($attempt->current_component > 1) {
            // Bisa menampilkan opsi "Lanjut test sebelumnya"
            $canResume = true;
        } else {
            $canResume = false;
        }

        return view('student.profiling-test.intro', compact('course', 'canResume', 'attempt'));
    }

    public function start(Course $course)
    {
        $access = $this->checkAccess($course);
        if ($access['completed']) {
            return redirect()->route('student.courses.show', $course->slug);
        }

        $attempt = $access['attempt'];
        return redirect()->route('student.profiling-test.component', [$course->slug, $attempt->current_component]);
    }

    public function showComponent(Course $course, int $componentOrder)
    {
        $access = $this->checkAccess($course);
        if ($access['completed']) {
            return redirect()->route('student.courses.show', $course->slug);
        }

        $attempt = $access['attempt'];

        // Enforce sequential progression
        if ($componentOrder > $attempt->current_component) {
            return redirect()->route('student.profiling-test.component', [$course->slug, $attempt->current_component]);
        }

        if ($componentOrder == 2) {
            // Prior Knowledge MCQ
            $course->load('knowledgeQuestions.options');
            $questions = $course->knowledgeQuestions;
            if ($questions->isEmpty()) {
                // If instructor forgot to add questions, just skip this component
                $attempt->update(['current_component' => 3]);
                return redirect()->route('student.profiling-test.component', [$course->slug, 3]);
            }
            return view('student.profiling-test.component_mcq', compact('course', 'attempt', 'questions', 'componentOrder'));
        } else {
            // Likert (Goal Setting, SDT, AI Preference)
            $component = ProfilingComponent::where('order', $componentOrder)->with(['questions' => function($q) {
                $q->where('is_active', true)->orderBy('order');
            }])->firstOrFail();

            return view('student.profiling-test.component_likert', compact('course', 'attempt', 'component', 'componentOrder'));
        }
    }

    public function saveLikert(Request $request, Course $course, int $componentOrder)
    {
        $access = $this->checkAccess($course);
        if ($access['completed']) return redirect()->route('student.courses.show', $course->slug);

        $attempt = $access['attempt'];
        $component = ProfilingComponent::where('order', $componentOrder)->firstOrFail();

        $answers = $request->input('answers', []);
        
        foreach ($answers as $questionId => $value) {
            ProfilingLikertAnswer::updateOrCreate(
                ['attempt_id' => $attempt->id, 'question_id' => $questionId],
                ['answer_value' => $value]
            );
        }

        // Move to next component
        $nextComponent = $componentOrder == 1 ? 2 : ($componentOrder == 3 ? 4 : 5);
        $attempt->update(['current_component' => $nextComponent]);

        if ($nextComponent > 4) {
            return redirect()->route('student.profiling-test.submit', $course->slug);
        }

        return redirect()->route('student.profiling-test.component', [$course->slug, $nextComponent]);
    }

    public function saveMcq(Request $request, Course $course)
    {
        $access = $this->checkAccess($course);
        if ($access['completed']) return redirect()->route('student.courses.show', $course->slug);

        $attempt = $access['attempt'];
        $answers = $request->input('answers', []);

        foreach ($answers as $questionId => $optionId) {
            $isCorrect = \App\Models\CourseKnowledgeOption::where('id', $optionId)->value('is_correct');
            
            ProfilingMcqAnswer::updateOrCreate(
                ['attempt_id' => $attempt->id, 'question_id' => $questionId],
                ['selected_option_id' => $optionId, 'is_correct' => $isCorrect]
            );
        }

        // Move to next component (3)
        $attempt->update(['current_component' => 3]);
        return redirect()->route('student.profiling-test.component', [$course->slug, 3]);
    }

    public function submit(Course $course)
    {
        $access = $this->checkAccess($course);
        if ($access['completed']) return redirect()->route('student.courses.show', $course->slug);

        $attempt = $access['attempt'];
        
        // Calculate scores
        $this->scoreService->computeAndSave($attempt);

        // Mark as completed
        $attempt->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('student.profiling-test.thankyou', $course->slug);
    }

    public function thankyou(Course $course)
    {
        return view('student.profiling-test.thankyou', compact('course'));
    }
}

<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionTopic;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class QuizQuestionController extends Controller
{
    public function index(Quiz $quiz)
    {
        // Otorisasi dihapus
        $quiz->loadMissing('lesson.module.course');
        Gate::authorize('manage', $quiz->lesson->module->course);

        $attachedQuestions = $quiz->questions()->orderBy('pivot_order')->get();

        return view('instructor.quizzes.manage-questions', compact('quiz', 'attachedQuestions'));
    }

    /**
     * Menampilkan halaman untuk memilih soal dari Bank Soal.
     * Ini akan menampilkan daftar topik soal yang relevan untuk kursus ini.
     */
    public function browseBank(Request $request, Quiz $quiz)
    {
        // Ambil ID kursus saat ini dari relasi
        $quiz->loadMissing('lesson.module.course');
        $course = $quiz->lesson->module->course;
        Gate::authorize('manage', $course);

        $currentCourseId = $course->id;
        $user = Auth::user();

        // Ambil ID soal yang sudah ada di kuis ini untuk di-exclude
        $existingQuestionIds = $quiz->questions()->pluck('questions.id');

        // --- LOGIKA BARU UNTUK FILTER TOPIK ---
        $topics = $user->questionTopics()
            ->where(function ($query) use ($currentCourseId) {
                // Kondisi 1: Topik tersedia untuk semua kursus
                $query->where('available_for_all_courses', true)
                    // ATAU
                    // Kondisi 2: Topik terhubung secara spesifik ke kursus ini
                    ->orWhereHas('courses', function ($subQuery) use ($currentCourseId) {
                        $subQuery->where('course_id', $currentCourseId);
                    });
            })
            ->withCount(['questions' => function ($query) use ($existingQuestionIds) {
                // Hitung hanya soal yang BELUM ada di kuis ini
                $query->whereNotIn('id', $existingQuestionIds);
            }])
            ->latest()
            ->get();
        // --- AKHIR LOGIKA BARU ---

        // Jika ada parameter 'topic_id', tampilkan juga soal-soal dari topik tersebut
        $questionsInTopic = null;
        if ($request->has('topic_id')) {
            $selectedTopic = QuestionTopic::findOrFail($request->topic_id);
            // Pastikan instruktur adalah pemilik topik yang dipilih
            if ($user->id == $selectedTopic->instructor_id) {
                $questionsInTopic = $selectedTopic->questions()
                    ->whereNotIn('id', $existingQuestionIds) // Hanya tampilkan soal yang belum ditambahkan
                    ->get();
            }
        }

        return view('instructor.quizzes.browse-bank', compact('quiz', 'topics', 'questionsInTopic'));
    }

    public function attachQuestions(Request $request, Quiz $quiz)
    {
        // Otorisasi dihapus
        $quiz->loadMissing('lesson.module.course');
        Gate::authorize('manage', $quiz->lesson->module->course);

        $validated = $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id',
        ]);

        $ownedQuestionsCount = Question::whereIn('id', $validated['question_ids'])
            ->whereHas('topic', function ($query) {
                $query->where('instructor_id', Auth::id());
            })
            ->count();

        if ($ownedQuestionsCount !== count($validated['question_ids'])) {
            abort(403, 'Anda tidak memiliki izin untuk menambahkan soal ini.');
        }

        $quiz->questions()->syncWithoutDetaching($validated['question_ids']);

        return redirect()->route('instructor.quizzes.manage_questions', $quiz)->with('success', 'Soal berhasil ditambahkan ke kuis.');
    }

    public function detachQuestion(Quiz $quiz, Question $question)
    {
        // Otorisasi dihapus
        $quiz->loadMissing('lesson.module.course');
        Gate::authorize('manage', $quiz->lesson->module->course);

        $quiz->questions()->detach($question->id);

        return back()->with('success', 'Soal berhasil dihapus dari kuis.');
    }
}

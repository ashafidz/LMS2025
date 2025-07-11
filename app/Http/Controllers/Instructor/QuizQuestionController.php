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
    /**
     * Menampilkan halaman utama untuk mengelola soal dalam sebuah kuis.
     */
    public function index(Quiz $quiz)
    {
        // Otorisasi: Pastikan instruktur adalah pemilik kuis
        // Kita periksa melalui relasi lesson -> module -> course -> instructor
        if (Auth::id() !== $quiz->lesson->module->course->instructor_id) {
            abort(403, 'Akses ditolak.');
        }

        // Ambil semua soal yang sudah ada di kuis ini, diurutkan berdasarkan pivot table
        $attachedQuestions = $quiz->questions()->orderBy('pivot_order')->get();

        return view('instructor.quizzes.manage-questions', compact('quiz', 'attachedQuestions'));
    }

    /**
     * Menampilkan halaman untuk memilih soal dari Bank Soal.
     * Ini akan menampilkan daftar topik soal milik instruktur.
     */
    public function browseBank(Request $request, Quiz $quiz)
    {
        if (Auth::id() !== $quiz->lesson->module->course->instructor_id) {
            abort(403, 'Akses ditolak.');
        }

        // Ambil ID soal yang sudah ada di kuis ini untuk di-exclude
        $existingQuestionIds = $quiz->questions()->pluck('questions.id');

        // Ambil semua topik soal milik instruktur
        $topics = Auth::user()->questionTopics()->withCount(['questions' => function ($query) use ($existingQuestionIds) {
            // Hitung hanya soal yang BELUM ada di kuis ini
            $query->whereNotIn('id', $existingQuestionIds);
        }])->get();

        // Jika ada parameter 'topic_id', tampilkan juga soal-soal dari topik tersebut
        $questionsInTopic = null;
        if ($request->has('topic_id')) {
            $selectedTopic = QuestionTopic::findOrFail($request->topic_id);
            // Pastikan instruktur adalah pemilik topik yang dipilih
            if (Auth::id() === $selectedTopic->instructor_id) {
                $questionsInTopic = $selectedTopic->questions()
                    ->whereNotIn('id', $existingQuestionIds) // Hanya tampilkan soal yang belum ditambahkan
                    ->get();
            }
        }

        return view('instructor.quizzes.browse-bank', compact('quiz', 'topics', 'questionsInTopic'));
    }

    /**
     * Menyimpan (melampirkan) soal-soal yang dipilih dari bank ke dalam kuis.
     */
    public function attachQuestions(Request $request, Quiz $quiz)
    {
        if (Auth::id() !== $quiz->lesson->module->course->instructor_id) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id', // Pastikan semua ID soal valid
        ]);

        // Gunakan syncWithoutDetaching untuk menambahkan soal baru tanpa menghapus yang lama
        $quiz->questions()->syncWithoutDetaching($validated['question_ids']);

        return redirect()->route('instructor.quizzes.manage_questions', $quiz)->with('success', 'Soal berhasil ditambahkan ke kuis.');
    }

    /**
     * Menghapus (melepas) satu soal dari kuis.
     */
    public function detachQuestion(Quiz $quiz, Question $question)
    {
        if (Auth::id() !== $quiz->lesson->module->course->instructor_id) {
            abort(403, 'Akses ditolak.');
        }

        // Gunakan detach untuk menghapus relasi di pivot table
        $quiz->questions()->detach($question->id);

        return back()->with('success', 'Soal berhasil dihapus dari kuis.');
    }
}

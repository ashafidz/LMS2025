<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\QuestionTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            ->withCount('questions')
            ->latest()
            ->paginate(10);

        return view('instructor.question_bank.topics.index', compact('topics'));
    }

    /**
     * Menampilkan form untuk membuat topik baru.
     */
    public function create()
    {
        // Ambil semua kursus milik instruktur untuk ditampilkan di form
        $courses = Auth::user()->courses()->orderBy('title')->get();
        return view('instructor.question_bank.topics.create', compact('courses'));
    }

    /**
     * Menyimpan topik baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'available_for_all_courses' => 'boolean',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'exists:courses,id',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $topic = Auth::user()->questionTopics()->create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'available_for_all_courses' => $request->has('available_for_all_courses'),
            ]);

            // Jika tidak tersedia untuk semua, hubungkan ke kursus yang dipilih
            if (!$topic->available_for_all_courses && !empty($validated['course_ids'])) {
                $topic->courses()->sync($validated['course_ids']);
            }
        });

        return redirect()->route('instructor.question-bank.topics.index')
            ->with('success', 'Topik soal berhasil dibuat.');
    }

    public function show(QuestionTopic $topic)
    {
        return redirect()->route('instructor.question-bank.questions.index', $topic);
    }

    /**
     * Menampilkan form untuk mengedit topik.
     */
    public function edit(QuestionTopic $topic)
    {
        // Eager load relasi kursus yang sudah terhubung
        $topic->load('courses');
        $courses = Auth::user()->courses()->orderBy('title')->get();
        return view('instructor.question_bank.topics.edit', compact('topic', 'courses'));
    }

    /**
     * Memperbarui topik di database.
     */
    public function update(Request $request, QuestionTopic $topic)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'available_for_all_courses' => 'boolean',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'exists:courses,id',
        ]);

        DB::transaction(function () use ($validated, $request, $topic) {
            $topic->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'available_for_all_courses' => $request->has('available_for_all_courses'),
            ]);

            // Jika tidak tersedia untuk semua, hubungkan ke kursus yang dipilih
            if (!$topic->available_for_all_courses && !empty($validated['course_ids'])) {
                $topic->courses()->sync($validated['course_ids']);
            } else {
                // Jika dicentang "semua kursus", hapus semua relasi spesifik
                $topic->courses()->detach();
            }
        });

        return redirect()->route('instructor.question-bank.topics.index')
            ->with('success', 'Topik soal berhasil diperbarui.');
    }

    public function destroy(QuestionTopic $topic)
    {
        $topic->delete();
        return redirect()->route('instructor.question-bank.topics.index')
            ->with('success', 'Topik soal berhasil dihapus.');
    }
}

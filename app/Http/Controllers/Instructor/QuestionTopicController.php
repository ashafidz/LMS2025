<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\QuestionTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionTopicController extends Controller
{
    /**
     * Menampilkan daftar topik soal dengan filter berdasarkan kursus.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->query('filter_course');

        // Mulai query dasar untuk topik milik instruktur
        $query = $user->questionTopics()
            ->withExists(['questions as is_locked' => fn($q) => $q->whereHas('quizzes')])
            ->withCount('questions');

        // Terapkan filter berdasarkan pilihan dropdown
        if ($filter === 'global') {
            // Hanya tampilkan topik yang tersedia untuk semua kursus
            $query->where('available_for_all_courses', true);
        } elseif (is_numeric($filter)) {
            // Tampilkan topik yang global ATAU yang terhubung ke kursus spesifik ini
            $query->where(function ($q) use ($filter) {
                $q->where('available_for_all_courses', true)
                    ->orWhereHas('courses', function ($subQ) use ($filter) {
                        $subQ->where('course_id', $filter);
                    });
            });
        }
        // Jika filter adalah 'all' atau kosong, tidak perlu filter tambahan (tampilkan semua)

        $topics = $query->latest()->paginate(10)->withQueryString();

        // Ambil semua kursus milik instruktur untuk mengisi dropdown
        $courses = $user->courses()->orderBy('title')->get();

        return view('instructor.question_bank.topics.index', compact('topics', 'courses'));
    }

    /**
     * Menampilkan form untuk membuat topik baru.
     */
    public function create()
    {
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

            if (!$topic->available_for_all_courses && !empty($validated['course_ids'])) {
                $topic->courses()->sync($validated['course_ids']);
            } else {
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

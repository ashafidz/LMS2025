<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\AdaptiveLesson;
use App\Models\AdaptiveModule;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdaptiveContentController extends Controller
{
    /**
     * Daftar 6 archetype yang selalu ditampilkan (Opsi B: hardcoded, tidak tergantung K-Means run).
     */
    public const ARCHETYPES = [
        'Expert Innovator'       => 'Siswa Expert yang sangat committed ke semua fitur AI personalisasi. Semua preferensi AI bernilai sangat tinggi (>4.5).',
        'Adaptive AI Explorer'   => 'Siswa Expert yang aktif menggunakan semua fitur AI. Preferensi AI secara keseluruhan High namun tidak sekuat Expert Innovator.',
        'Guided Mastery Expert'  => 'Siswa Expert yang tetap suka dibimbing AI secara terstruktur. Guidance dan Adaptivity High, meski Transparency Medium.',
        'Selective AI Partner'   => 'Siswa Expert yang selektif menggunakan AI hanya saat benar-benar butuh. Preferensi AI secara keseluruhan Medium.',
        'Achievement Challenger' => 'Siswa berorientasi nilai dan kompetisi (Performance Goal > Mastery Goal). Menggunakan AI sebagai alat benchmark.',
        'Guided Growth Learner'  => 'Siswa dengan prior knowledge rendah (<75%). Membutuhkan scaffolding dan bimbingan intensif.',
    ];

    // ─── Guard Helper ────────────────────────────────────────

    private function authorizeAdaptiveCourse(Course $course): void
    {
        abort_if($course->instructor_id !== Auth::id(), 403);
        abort_if($course->type !== 'adaptive', 404, 'Kursus ini bukan kursus Adaptive.');
    }

    // ─── INDEX ───────────────────────────────────────────────

    /**
     * Halaman utama: pilih archetype & kelola modul/lesson-nya.
     */
    public function index(Course $course)
    {
        $this->authorizeAdaptiveCourse($course);

        $modules = $course->adaptiveModules()
                          ->with('lessons')
                          ->orderBy('order')
                          ->get();

        $activeJob = \App\Models\AiGenerationJob::where('course_id', $course->id)
            ->whereIn('status', ['queued', 'processing'])
            ->first();

        $jobHistory = \App\Models\AiGenerationJob::where('course_id', $course->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $references = \App\Models\AiReference::where('course_id', $course->id)->get();

        return view('instructor.adaptive.index', [
            'course'          => $course,
            'archetypes'      => self::ARCHETYPES,
            'modules'         => $modules,
            'activeJob'       => $activeJob,
            'jobHistory'      => $jobHistory,
            'references'      => $references,
        ]);
    }

    // ─── MODULE CRUD ─────────────────────────────────────────

    public function storeModule(Course $course, Request $request)
    {
        $this->authorizeAdaptiveCourse($course);

        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'target_archetypes' => 'nullable|array',
            'target_archetypes.*' => 'string'
        ]);

        $lastOrder = AdaptiveModule::where('course_id', $course->id)->max('order') ?? -1;

        $course->adaptiveModules()->create([
            'title'             => $validated['title'],
            'description'       => $validated['description'] ?? null,
            'target_archetypes' => $validated['target_archetypes'] ?? [],
            'order'             => $lastOrder + 1,
            'ai_generated'      => false,
        ]);

        return redirect()
            ->route('instructor.adaptive.index', $course)
            ->with('success', 'Modul berhasil ditambahkan.');
    }

    public function updateModule(Course $course, AdaptiveModule $module, Request $request)
    {
        $this->authorizeAdaptiveCourse($course);
        abort_if($module->course_id !== $course->id, 403);

        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'target_archetypes' => 'nullable|array',
            'target_archetypes.*' => 'string'
        ]);

        $module->update([
            'title'             => $validated['title'],
            'description'       => $validated['description'] ?? null,
            'target_archetypes' => $validated['target_archetypes'] ?? [],
        ]);

        return redirect()
            ->route('instructor.adaptive.index', $course)
            ->with('success', 'Modul berhasil diperbarui.');
    }

    public function destroyModule(Course $course, AdaptiveModule $module)
    {
        $this->authorizeAdaptiveCourse($course);
        abort_if($module->course_id !== $course->id, 403);

        $module->delete(); // cascade → lessons ikut terhapus

        return redirect()
            ->route('instructor.adaptive.index', $course)
            ->with('success', 'Modul dan semua lesson-nya berhasil dihapus.');
    }

    // ─── LESSON CRUD ─────────────────────────────────────────

    public function storeLesson(Course $course, AdaptiveModule $module, Request $request)
    {
        $this->authorizeAdaptiveCourse($course);
        abort_if($module->course_id !== $course->id, 403);

        $rules = [
            'lesson_type' => 'required|in:article,assignment,quiz,video,lessonpoin,document,link',
            'video_url'   => 'nullable|url|max:500',
            'lessonpoin_title' => 'nullable|string|max:255',
            'lessonpoin_description' => 'nullable|string',
            'title'       => 'required|string|max:255',
            'content'     => 'nullable|string',
            'assignment_max_score' => 'nullable|integer|min:1',
            'assignment_instructions' => 'nullable|string',
            'document_file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:20480',
        ];

        if ($request->input('lesson_type') === 'link') {
            $rules['links'] = 'required|array|min:1';
            $rules['links.*.title'] = 'required|string|max:255';
            $rules['links.*.url'] = 'required|url';
        }

        $validated = $request->validate($rules);

        $lessonData = $validated;
        unset($lessonData['document_file'], $lessonData['links']);

        if ($request->hasFile('document_file') && $validated['lesson_type'] === 'document') {
            $lessonData['document_path'] = $request->file('document_file')->store('lesson_documents', 'public');
        }

        if ($validated['lesson_type'] === 'link' && !empty($validated['links'])) {
            $lessonData['link_data'] = array_values(array_filter($validated['links'], function($l) { return !empty($l['title']) && !empty($l['url']); }));
        }

        $lastOrder = AdaptiveLesson::where('adaptive_module_id', $module->id)->max('order') ?? -1;

        $module->lessons()->create([
            ...$lessonData,
            'order'        => $lastOrder + 1,
            'ai_generated' => false,
        ]);

        return redirect()
            ->route('instructor.adaptive.index', $course)
            ->with('success', 'Lesson berhasil ditambahkan.');
    }

    public function updateLesson(Course $course, AdaptiveLesson $lesson, Request $request)
    {
        $this->authorizeAdaptiveCourse($course);
        abort_if($lesson->module->course_id !== $course->id, 403);

        $rules = [
            'lesson_type' => 'required|in:article,assignment,quiz,video,lessonpoin,document,link',
            'video_url'   => 'nullable|url|max:500',
            'lessonpoin_title' => 'nullable|string|max:255',
            'lessonpoin_description' => 'nullable|string',
            'title'       => 'required|string|max:255',
            'content'     => 'nullable|string',
            'assignment_max_score' => 'nullable|integer|min:1',
            'assignment_instructions' => 'nullable|string',
            'document_file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:20480',
        ];

        if ($request->input('lesson_type') === 'link') {
            $rules['links'] = 'required|array|min:1';
            $rules['links.*.title'] = 'required|string|max:255';
            $rules['links.*.url'] = 'required|url';
        }

        $validated = $request->validate($rules);

        $lessonData = $validated;
        unset($lessonData['document_file'], $lessonData['links']);

        if ($request->hasFile('document_file') && $validated['lesson_type'] === 'document') {
            $lessonData['document_path'] = $request->file('document_file')->store('lesson_documents', 'public');
        }

        if ($validated['lesson_type'] === 'link' && !empty($validated['links'])) {
            $lessonData['link_data'] = array_values(array_filter($validated['links'], function($l) { return !empty($l['title']) && !empty($l['url']); }));
        }

        $lesson->update($lessonData);

        return redirect()
            ->route('instructor.adaptive.index', $course)
            ->with('success', 'Lesson berhasil diperbarui.');
    }

    public function destroyLesson(Course $course, AdaptiveLesson $lesson)
    {
        $this->authorizeAdaptiveCourse($course);
        abort_if($lesson->module->course_id !== $course->id, 403);

        $lesson->delete();

        return redirect()
            ->route('instructor.adaptive.index', $course)
            ->with('success', 'Lesson berhasil dihapus.');
    }

    // ─── QUIZ MANAGEMENT ─────────────────────────────────────

    public function manageQuiz(Course $course, AdaptiveLesson $lesson)
    {
        $this->authorizeAdaptiveCourse($course);
        abort_if($lesson->module->course_id !== $course->id, 403);
        abort_if($lesson->lesson_type !== 'quiz', 400, 'Lesson ini bukan bertipe Quiz.');

        return view('instructor.adaptive.quiz', compact('course', 'lesson'));
    }

    public function updateQuiz(Course $course, AdaptiveLesson $lesson, Request $request)
    {
        $this->authorizeAdaptiveCourse($course);
        abort_if($lesson->module->course_id !== $course->id, 403);
        abort_if($lesson->lesson_type !== 'quiz', 400, 'Lesson ini bukan bertipe Quiz.');

        $validated = $request->validate([
            'questions' => 'nullable|array',
            'questions.*.question_text' => 'required|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*' => 'required|string',
            'questions.*.correct_answer_index' => 'required|integer|min:0',
            'questions.*.explanation' => 'nullable|string',
        ]);

        $lesson->update([
            'quiz_data' => $validated['questions'] ?? [],
        ]);

        return redirect()
            ->route('instructor.adaptive.index', $course)
            ->with('success', 'Soal Quiz berhasil diperbarui.');
    }
}

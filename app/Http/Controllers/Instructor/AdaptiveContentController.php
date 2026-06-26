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
    public function index(Course $course, Request $request)
    {
        $this->authorizeAdaptiveCourse($course);

        $activeArchetype = $request->get('archetype', array_key_first(self::ARCHETYPES));

        // Pastikan archetype yang diminta valid
        if (!array_key_exists($activeArchetype, self::ARCHETYPES)) {
            $activeArchetype = array_key_first(self::ARCHETYPES);
        }

        $modules = $course->adaptiveModulesFor($activeArchetype)
                          ->with('lessons')
                          ->orderBy('order')
                          ->get();

        $activeJob = \App\Models\AiGenerationJob::where('course_id', $course->id)
            ->where('archetype_name', $activeArchetype)
            ->whereIn('status', ['queued', 'processing'])
            ->first();

        $jobHistory = \App\Models\AiGenerationJob::where('course_id', $course->id)
            ->where('archetype_name', $activeArchetype)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $references = \App\Models\AiReference::where('course_id', $course->id)
            ->where(function($q) use ($activeArchetype) {
                $q->where('archetype_name', $activeArchetype)
                  ->orWhereNull('archetype_name');
            })->get();

        return view('instructor.adaptive.index', [
            'course'          => $course,
            'archetypes'      => self::ARCHETYPES,
            'activeArchetype' => $activeArchetype,
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
            'archetype_name' => ['required', 'string', function ($attr, $value, $fail) {
                if (!array_key_exists($value, self::ARCHETYPES)) $fail('Archetype tidak valid.');
            }],
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
        ]);

        // Tentukan order: setelah modul terakhir untuk archetype ini
        $lastOrder = AdaptiveModule::where('course_id', $course->id)
                                   ->where('archetype_name', $validated['archetype_name'])
                                   ->max('order') ?? -1;

        $course->adaptiveModules()->create([
            ...$validated,
            'order'        => $lastOrder + 1,
            'ai_generated' => false,
        ]);

        return redirect()
            ->route('instructor.adaptive.index', [$course, 'archetype' => $validated['archetype_name']])
            ->with('success', 'Modul berhasil ditambahkan.');
    }

    public function updateModule(Course $course, AdaptiveModule $module, Request $request)
    {
        $this->authorizeAdaptiveCourse($course);
        abort_if($module->course_id !== $course->id, 403);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $module->update($validated);

        return redirect()
            ->route('instructor.adaptive.index', [$course, 'archetype' => $module->archetype_name])
            ->with('success', 'Modul berhasil diperbarui.');
    }

    public function destroyModule(Course $course, AdaptiveModule $module)
    {
        $this->authorizeAdaptiveCourse($course);
        abort_if($module->course_id !== $course->id, 403);

        $archetype = $module->archetype_name;
        $module->delete(); // cascade → lessons ikut terhapus

        return redirect()
            ->route('instructor.adaptive.index', [$course, 'archetype' => $archetype])
            ->with('success', 'Modul dan semua lesson-nya berhasil dihapus.');
    }

    // ─── LESSON CRUD ─────────────────────────────────────────

    public function storeLesson(Course $course, AdaptiveModule $module, Request $request)
    {
        $this->authorizeAdaptiveCourse($course);
        abort_if($module->course_id !== $course->id, 403);

        $validated = $request->validate([
            'lesson_type' => 'required|in:article,assignment,quiz',
            'title'       => 'required|string|max:255',
            'content'     => 'nullable|string',
        ]);

        $lastOrder = AdaptiveLesson::where('adaptive_module_id', $module->id)->max('order') ?? -1;

        $module->lessons()->create([
            ...$validated,
            'order'        => $lastOrder + 1,
            'ai_generated' => false,
        ]);

        return redirect()
            ->route('instructor.adaptive.index', [$course, 'archetype' => $module->archetype_name])
            ->with('success', 'Lesson berhasil ditambahkan.');
    }

    public function updateLesson(Course $course, AdaptiveLesson $lesson, Request $request)
    {
        $this->authorizeAdaptiveCourse($course);
        abort_if($lesson->module->course_id !== $course->id, 403);

        $validated = $request->validate([
            'lesson_type' => 'required|in:article,assignment,quiz',
            'title'       => 'required|string|max:255',
            'content'     => 'nullable|string',
        ]);

        $lesson->update($validated);

        return redirect()
            ->route('instructor.adaptive.index', [$course, 'archetype' => $lesson->module->archetype_name])
            ->with('success', 'Lesson berhasil diperbarui.');
    }

    public function destroyLesson(Course $course, AdaptiveLesson $lesson)
    {
        $this->authorizeAdaptiveCourse($course);
        abort_if($lesson->module->course_id !== $course->id, 403);

        $archetype = $lesson->module->archetype_name;
        $lesson->delete();

        return redirect()
            ->route('instructor.adaptive.index', [$course, 'archetype' => $archetype])
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
            ->route('instructor.adaptive.index', [$course, 'archetype' => $lesson->module->archetype_name])
            ->with('success', 'Soal Quiz berhasil diperbarui.');
    }
}

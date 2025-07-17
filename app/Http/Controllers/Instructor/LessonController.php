<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonArticle;
use App\Models\LessonAssignment;
use App\Models\LessonLinkCollection;
// Diperbarui: Menggunakan LessonDocument
use App\Models\LessonDocument;
use App\Models\LessonVideo;
use App\Models\Module;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    /**
     * Menampilkan daftar pelajaran untuk modul tertentu.
     */
    public function index(Module $module)
    {
        $lessons = $module->lessons()->orderBy('order')->get();
        return view('instructor.lessons.index', compact('module', 'lessons'));
    }

    /**
     * Menampilkan form pembuatan yang sesuai berdasarkan tipe pelajaran.
     */
    public function create(Request $request, Module $module)
    {
        $type = $request->query('type');
        // Diperbarui: Mengganti 'powerpoint' menjadi 'document'
        $validTypes = ['article', 'video', 'quiz', 'assignment', 'document', 'link'];

        if (!in_array($type, $validTypes)) {
            abort(404, 'Tipe pelajaran tidak valid.');
        }

        $viewName = "instructor.lessons.create-{$type}";
        return view($viewName, compact('module'));
    }

    /**
     * Menyimpan pelajaran baru ke database.
     */
    public function store(Request $request, Module $module)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            // Diperbarui: Mengganti 'powerpoint' menjadi 'document'
            'lesson_type' => 'required|in:article,video,quiz,assignment,document,link',
        ]);

        $lessonable = null;

        DB::transaction(function () use ($request, $module, &$lessonable) {
            $lessonType = $request->input('lesson_type');

            switch ($lessonType) {
                case 'article':
                    $validated = $request->validate(['content' => 'required|string']);
                    $lessonable = LessonArticle::create($validated);
                    break;

                case 'video':
                    $validated = $request->validate([
                        'source_type' => 'required|in:upload,youtube',
                        'video_file' => 'required_if:source_type,upload|file|mimes:mp4,mov,avi,wmv|max:102400',
                        'video_path' => 'required_if:source_type,youtube|url',
                    ]);
                    $sourceType = $validated['source_type'];
                    $path = ($sourceType === 'upload') ? $validated['video_file']->store('lesson_videos', 'public') : $validated['video_path'];
                    $lessonable = LessonVideo::create(['source_type' => $sourceType, 'video_path' => $path]);
                    break;

                case 'quiz':
                    $validated = $request->validate([
                        'quiz_title' => 'required|string|max:255',
                        'quiz_description' => 'nullable|string',
                        'pass_mark' => 'required|integer|min:0|max:100',
                        'time_limit' => 'nullable|integer|min:1',
                    ]);
                    $lessonable = Quiz::create([
                        'title' => $validated['quiz_title'],
                        'description' => $validated['quiz_description'],
                        'pass_mark' => $validated['pass_mark'],
                        'time_limit' => $validated['time_limit'],
                    ]);
                    break;

                case 'assignment':
                    $validated = $request->validate(['instructions' => 'required|string', 'due_date' => 'nullable|date']);
                    $lessonable = LessonAssignment::create($validated);
                    break;

                case 'document': // Diperbarui dari 'powerpoint'
                    $validated = $request->validate(['document_file' => 'required|file|mimes:pdf|max:20480']); // Hanya PDF, max 20MB
                    $path = $validated['document_file']->store('lesson_documents', 'public'); // Folder baru
                    $lessonable = LessonDocument::create(['file_path' => $path]);
                    break;

                case 'link':
                    $validated = $request->validate([
                        'links' => 'required|array|min:1',
                        'links.*.title' => 'required|string|max:255',
                        'links.*.url' => 'required|url',
                    ]);
                    $lessonable = LessonLinkCollection::create(['links' => $validated['links']]);
                    break;
            }

            $lastOrder = $module->lessons()->max('order') ?? 0;
            $lessonable->lesson()->create([
                'module_id' => $module->id,
                'title' => $request->input('title'),
                'order' => $lastOrder + 1,
            ]);
        });

        return redirect()->route('instructor.modules.lessons.index', $module)->with('success', 'Pelajaran berhasil dibuat.');
    }

    /**
     * Menampilkan form edit yang sesuai berdasarkan tipe pelajaran.
     */
    public function edit(Lesson $lesson)
    {
        $lesson->load('lessonable');
        $type = $lesson->lessonable_type;
        $shortType = strtolower(class_basename($type));

        $viewName = "instructor.lessons.edit-{$shortType}";
        return view($viewName, compact('lesson'));
    }

    /**
     * Memperbarui pelajaran yang ada di database.
     */
    public function update(Request $request, Lesson $lesson)
    {
        $request->validate(['title' => 'required|string|max:255']);

        DB::transaction(function () use ($request, $lesson) {
            $lesson->update(['title' => $request->input('title')]);
            $lessonable = $lesson->lessonable;
            $shortType = strtolower(class_basename($lessonable));

            switch ($shortType) {
                case 'lessonarticle':
                    $validated = $request->validate(['content' => 'required|string']);
                    $lessonable->update($validated);
                    break;

                case 'lessonvideo':
                    $validated = $request->validate([
                        'source_type' => 'required|in:upload,youtube',
                        'video_file' => 'nullable|required_if:source_type,upload|file|mimes:mp4,mov,avi|max:102400',
                        'video_path' => 'nullable|required_if:source_type,youtube|url',
                    ]);
                    $sourceType = $validated['source_type'];
                    $path = $lessonable->video_path;
                    if ($sourceType === 'upload' && $request->hasFile('video_file')) {
                        if ($lessonable->source_type === 'upload' && $lessonable->video_path) {
                            Storage::disk('public')->delete($lessonable->video_path);
                        }
                        $path = $validated['video_file']->store('lesson_videos', 'public');
                    } elseif ($sourceType === 'youtube') {
                        if ($lessonable->source_type === 'upload' && $lessonable->video_path) {
                            Storage::disk('public')->delete($lessonable->video_path);
                        }
                        $path = $validated['video_path'];
                    }
                    $lessonable->update(['source_type' => $sourceType, 'video_path' => $path]);
                    break;

                case 'quiz':
                    $validated = $request->validate([
                        'quiz_title' => 'required|string|max:255',
                        'quiz_description' => 'nullable|string',
                        'pass_mark' => 'required|integer|min:0|max:100',
                        'time_limit' => 'nullable|integer|min:1',
                    ]);
                    $lessonable->update([
                        'title' => $validated['quiz_title'],
                        'description' => $validated['quiz_description'],
                        'pass_mark' => $validated['pass_mark'],
                        'time_limit' => $validated['time_limit'],
                    ]);
                    break;

                case 'lessonassignment':
                    $validated = $request->validate(['instructions' => 'required|string', 'due_date' => 'nullable|date']);
                    $lessonable->update($validated);
                    break;

                case 'lessondocument': // Diperbarui dari 'lessonpowerpoint'
                    if ($request->hasFile('document_file')) {
                        $validated = $request->validate(['document_file' => 'required|file|mimes:pdf|max:20480']);
                        Storage::disk('public')->delete($lessonable->file_path);
                        $path = $validated['document_file']->store('lesson_documents', 'public');
                        $lessonable->update(['file_path' => $path]);
                    }
                    break;

                case 'lessonlinkcollection':
                    $validated = $request->validate([
                        'links' => 'required|array|min:1',
                        'links.*.title' => 'required|string|max:255',
                        'links.*.url' => 'required|url',
                    ]);
                    $lessonable->update(['links' => $validated['links']]);
                    break;
            }
        });

        return redirect()->route('instructor.modules.lessons.index', $lesson->module)->with('success', 'Pelajaran berhasil diperbarui.');
    }

    /**
     * Menghapus pelajaran dari database.
     */
    public function destroy(Lesson $lesson)
    {
        DB::transaction(function () use ($lesson) {
            $lessonable = $lesson->lessonable;
            if ($lessonable) {
                $shortType = strtolower(class_basename($lessonable));
                if ($shortType === 'lessonvideo' && $lessonable->source_type === 'upload') {
                    Storage::disk('public')->delete($lessonable->video_path);
                } elseif ($shortType === 'lessondocument') { // Diperbarui dari 'lessonpowerpoint'
                    Storage::disk('public')->delete($lessonable->file_path);
                }
                $lessonable->delete();
            }
            $lesson->delete();
        });

        return back()->with('success', 'Pelajaran berhasil dihapus.');
    }

    /**
     * Memperbarui urutan pelajaran.
     */
    public function reorder(Request $request, Module $module)
    {
        $request->validate([
            'lesson_ids' => 'required|array',
            'lesson_ids.*' => 'exists:lessons,id',
        ]);

        foreach ($request->lesson_ids as $index => $lessonId) {
            Lesson::where('id', $lessonId)
                ->where('module_id', $module->id)
                ->update(['order' => $index + 1]);
        }

        return response()->json(['status' => 'success', 'message' => 'Urutan pelajaran diperbarui.']);
    }
}

<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonArticle;
use App\Models\LessonAssignment;
use App\Models\LessonVideo;
use App\Models\Module;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    /**
     * Menampilkan daftar pelajaran untuk modul tertentu.
     */
    public function index(Module $module)
    {
        // Otorisasi: Pastikan instruktur yang login adalah pemilik kursus dari modul ini
        if (Auth::id() !== $module->course->instructor_id) {
            abort(403, 'Akses ditolak.');
        }

        $lessons = $module->lessons()->orderBy('order')->get();

        return view('instructor.lessons.index', compact('module', 'lessons'));
    }

    /**
     * Menampilkan form pembuatan yang sesuai berdasarkan tipe pelajaran.
     */
    public function create(Request $request, Module $module)
    {
        if (Auth::id() !== $module->course->instructor_id) {
            abort(403, 'Akses ditolak.');
        }

        $type = $request->query('type');
        $validTypes = ['article', 'video', 'quiz', 'assignment'];

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
        if (Auth::id() !== $module->course->instructor_id) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'lesson_type' => 'required|in:article,video,quiz,assignment',
        ]);

        $lessonable = null;

        // Menggunakan DB Transaction untuk memastikan semua data tersimpan atau tidak sama sekali
        DB::transaction(function () use ($request, $module, &$lessonable) {
            $lessonType = $request->input('lesson_type');

            // Logika SWITCH CASE untuk menangani setiap tipe pelajaran
            switch ($lessonType) {
                case 'article':
                    $validated = $request->validate(['content' => 'required|string']);
                    $lessonable = LessonArticle::create(['content' => $validated['content']]);
                    break;

                case 'video':
                    $validated = $request->validate(['video_file' => 'required|file|mimes:mp4,mov,avi,wmv|max:102400']); // max 100MB
                    // Simpan file video ke storage lokal
                    $path = $validated['video_file']->store('lesson_videos', 'public');
                    $lessonable = LessonVideo::create(['video_s3_key' => $path]); // Kita tetap gunakan kolom yang sama
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
                    $validated = $request->validate([
                        'instructions' => 'required|string',
                        'due_date' => 'nullable|date',
                    ]);
                    $lessonable = LessonAssignment::create($validated);
                    break;
            }

            // Buat record utama di tabel 'lessons' setelah konten spesifik dibuat
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
        if (Auth::id() !== $lesson->module->course->instructor_id) {
            abort(403, 'Akses ditolak.');
        }

        $lesson->load('lessonable'); // Eager load a polymorphic relationship
        $type = $lesson->lessonable_type; // e.g., "App\Models\LessonVideo"

        // Ambil nama pendek dari tipe (video, article, dll.)
        $shortType = strtolower(class_basename($type));

        $viewName = "instructor.lessons.edit-{$shortType}";
        return view($viewName, compact('lesson'));
    }

    /**
     * Memperbarui pelajaran yang ada di database.
     */
    public function update(Request $request, Lesson $lesson)
    {
        if (Auth::id() !== $lesson->module->course->instructor_id) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate(['title' => 'required|string|max:255']);

        DB::transaction(function () use ($request, $lesson) {
            // Update judul pelajaran utama
            $lesson->update(['title' => $request->input('title')]);

            $lessonable = $lesson->lessonable;
            $shortType = strtolower(class_basename($lessonable));

            // Logika SWITCH CASE untuk memperbarui konten spesifik
            switch ($shortType) {
                case 'lessonarticle':
                    $validated = $request->validate(['content' => 'required|string']);
                    $lessonable->update($validated);
                    break;

                case 'lessonvideo':
                    if ($request->hasFile('video_file')) {
                        $validated = $request->validate(['video_file' => 'required|file|mimes:mp4,mov,avi|max:102400']);
                        // Hapus video lama
                        Storage::disk('public')->delete($lessonable->video_s3_key);
                        // Simpan video baru
                        $path = $validated['video_file']->store('lesson_videos', 'public');
                        $lessonable->update(['video_s3_key' => $path]);
                    }
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
                    $validated = $request->validate([
                        'instructions' => 'required|string',
                        'due_date' => 'nullable|date',
                    ]);
                    $lessonable->update($validated);
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
        if (Auth::id() !== $lesson->module->course->instructor_id) {
            abort(403, 'Akses ditolak.');
        }

        DB::transaction(function () use ($lesson) {
            // Hapus konten spesifik (video, artikel, dll.)
            $lessonable = $lesson->lessonable;
            if ($lessonable) {
                // Jika ini adalah video, hapus juga filenya dari storage
                if (strtolower(class_basename($lessonable)) === 'lessonvideo') {
                    Storage::disk('public')->delete($lessonable->video_s3_key);
                }
                $lessonable->delete();
            }
            // Hapus record pelajaran utama
            $lesson->delete();
        });

        return back()->with('success', 'Pelajaran berhasil dihapus.');
    }

    /**
     * Memperbarui urutan pelajaran.
     */
    public function reorder(Request $request, Module $module)
    {
        if (Auth::id() !== $module->course->instructor_id) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'lesson_ids' => 'required|array',
            'lesson_ids.*' => 'exists:lessons,id',
        ]);

        foreach ($request->lesson_ids as $index => $lessonId) {
            Lesson::where('id', $lessonId)
                ->where('module_id', $module->id) // Keamanan ekstra
                ->update(['order' => $index + 1]);
        }

        return response()->json(['status' => 'success', 'message' => 'Urutan pelajaran diperbarui.']);
    }
}

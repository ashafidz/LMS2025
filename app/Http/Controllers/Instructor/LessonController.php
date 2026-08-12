<?php

namespace App\Http\Controllers\Instructor;

use App\Models\Quiz;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\LessonPoint;
use App\Models\LessonVideo;
// Diperbarui: Menggunakan LessonDocument
use Illuminate\Http\Request;
use App\Models\LessonArticle;
use App\Models\LessonDocument;
use Illuminate\Support\Carbon;
use App\Models\LessonAssignment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\LessonLinkCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    /**
     * Menampilkan daftar pelajaran untuk modul tertentu.
     */
    public function index(Module $module)
    {
        $lessons = $module->lessons()->orderBy('order')->get();
        $is_preview = false;
        return view('instructor.lessons.index', compact('module', 'lessons', 'is_preview'));
    }

    /**
     * Menampilkan form pembuatan yang sesuai berdasarkan tipe pelajaran.
     */
    public function create(Request $request, Module $module)
    {
        $type = $request->query('type');
        // Diperbarui: Mengganti 'powerpoint' menjadi 'document'
        $validTypes = ['article', 'video', 'quiz', 'assignment', 'document', 'link', 'lessonpoin', 'polling', 'wordcloud'];

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
            'lesson_type' => 'required|in:article,video,quiz,assignment,document,link,lessonpoin,polling,wordcloud',
        ]);

        $lessonType = $request->input('lesson_type');
        $documentFile = null;
        $documentDedupKey = null;

        if ($lessonType === 'document') {
            $validatedDocument = $request->validate([
                'document_file' => 'required|file|mimes:pdf|max:20480',
            ]);
            $documentFile = $validatedDocument['document_file'];
            $documentHash = hash_file('sha1', $documentFile->getRealPath());
            $titleHash = md5($request->input('title'));
            $documentDedupKey = 'lesson_document:' . Auth::id() . ':' . $module->id . ':' . $documentHash . ':' . $titleHash;

            if (!Cache::add($documentDedupKey, true, now()->addSeconds(30))) {
                return redirect()
                    ->route('instructor.modules.lessons.index', $module)
                    ->with('warning', 'Pelajaran sedang diproses atau sudah dibuat. Silakan cek daftar pelajaran.');
            }
        }

        $lessonable = null;
        try {
            DB::transaction(function () use ($request, $module, $lessonType, $documentFile, &$lessonable) {
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
                            'allow_exceed_time_limit' => 'required|boolean',
                            'reveal_answers' => 'required|boolean',
                            'max_attempts' => 'nullable|integer|min:1',
                            'available_from' => 'nullable|date', // Tambahkan validasi
                            'available_to' => 'nullable|date|after_or_equal:available_from', // Tambahkan validasi
                        ]);

                        // --- LOGIKA BARU DITAMBAHKAN DI SINI ---
                        if (!empty($validated['available_from'])) {
                            // 1. Ambil timezone instruktur (dari data user yang login)
                            $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');

                            // 2. Konversi waktu ke timezone instruktur
                            $availableFrom = Carbon::parse($validated['available_from'], $instructorTimezone)->utc();

                            // 3. Simpan waktu yang telah dikonversi
                            $validated['available_from'] = $availableFrom;
                        }

                        if (!empty($validated['available_to'])) {
                            // 1. Ambil timezone instruktur (dari data user yang login)
                            $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');

                            $availableTo = Carbon::parse($validated['available_to'], $instructorTimezone)->utc();
                            $validated['available_to'] = $availableTo;
                        }
                        // --- LOGIKA BARU DITAMBAHKAN DI SINI ---

                        $lessonable = Quiz::create([
                            'title' => $validated['quiz_title'],
                            'description' => $validated['quiz_description'],
                            'pass_mark' => $validated['pass_mark'],
                            'time_limit' => $validated['time_limit'],
                            'allow_exceed_time_limit' => $validated['allow_exceed_time_limit'],
                            'reveal_answers' => $validated['reveal_answers'],
                            'max_attempts' => $validated['max_attempts'],
                            'available_from' => $validated['available_from'], // Tambahkan validasi
                            'available_to' => $validated['available_to'], // Tambahkan validasi

                        ]);
                        break;

                    case 'assignment':
                        $validated = $request->validate(['instructions' => 'required|string', 'due_date' => 'nullable|date', 'pass_mark' => 'required|integer|min:0|max:100']);

                        // --- LOGIKA BARU DITAMBAHKAN DI SINI ---
                        if (!empty($validated['due_date'])) {
                            // 1. Ambil timezone instruktur (dari data user yang login)
                            $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');

                            // 2. Parse input sebagai waktu lokal, lalu konversi ke UTC untuk disimpan
                            $validated['due_date'] = Carbon::parse($validated['due_date'], $instructorTimezone)->utc();
                        }
                        // --- AKHIR LOGIKA BARU ---

                        $lessonable = LessonAssignment::create($validated);
                        break;

                    case 'document': // Diperbarui dari 'powerpoint'
                        $path = $documentFile->store('lesson_documents', 'public'); // Folder baru
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
                    case 'lessonpoin':
                        $validated = $request->validate([
                            'lessonpoin_title' => 'required|string|max:255',
                            'lessonpoin_description' => 'nullable|string',
                        ]);
                        $lessonable = \App\Models\LessonPoint::create([
                            'title' => $validated['lessonpoin_title'],
                            'description' => $validated['lessonpoin_description'],
                        ]);
                        break;
                    case 'polling':
                        $validated = $request->validate([
                            'polling_question' => 'required|string|max:255',
                            'polling_description' => 'nullable|string',
                            'polling_options' => 'required|array|min:2',
                            'polling_options.*' => 'required|string|max:255',
                            'is_active' => 'boolean',
                            'start_time' => 'nullable|date',
                            'end_time' => 'nullable|date|after_or_equal:start_time',
                            'show_voters' => 'boolean',
                            'show_results' => 'boolean',
                        ]);

                        $isActive = $request->has('is_active');
                        $showVoters = $request->has('show_voters');
                        $showResults = $request->has('show_results');
                        $startTime = null;
                        $endTime = null;

                        if (!empty($validated['start_time'])) {
                            $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');
                            $startTime = Carbon::parse($validated['start_time'], $instructorTimezone)->utc();
                        }
                        if (!empty($validated['end_time'])) {
                            $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');
                            $endTime = Carbon::parse($validated['end_time'], $instructorTimezone)->utc();
                        }

                        $lessonable = \App\Models\LessonPolling::create([
                            'question' => $validated['polling_question'],
                            'description' => $validated['polling_description'],
                            'is_active' => $isActive,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'show_voters' => $showVoters,
                            'show_results' => $showResults,
                        ]);

                        foreach ($validated['polling_options'] as $index => $optionText) {
                            $lessonable->options()->create([
                                'text' => $optionText,
                                'order' => $index,
                            ]);
                        }
                        break;
                    case 'wordcloud':
                        $validated = $request->validate([
                            'wordcloud_question' => 'required|string|max:255',
                            'wordcloud_description' => 'nullable|string',
                            'max_words' => 'required|integer|min:1|max:3',
                            'is_active' => 'boolean',
                            'start_time' => 'nullable|date',
                            'end_time' => 'nullable|date|after_or_equal:start_time',
                        ]);

                        $isActive = $request->has('is_active');
                        $startTime = null;
                        $endTime = null;

                        if (!empty($validated['start_time'])) {
                            $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');
                            $startTime = Carbon::parse($validated['start_time'], $instructorTimezone)->utc();
                        }
                        if (!empty($validated['end_time'])) {
                            $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');
                            $endTime = Carbon::parse($validated['end_time'], $instructorTimezone)->utc();
                        }

                        $lessonable = \App\Models\LessonWordcloud::create([
                            'question' => $validated['wordcloud_question'],
                            'description' => $validated['wordcloud_description'],
                            'max_words' => $validated['max_words'],
                            'is_active' => $isActive,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                        ]);
                        break;
                }

                $lastOrder = $module->lessons()->max('order') ?? 0;
                $lessonable->lesson()->create([
                    'module_id' => $module->id,
                    'title' => $request->input('title'),
                    'order' => $lastOrder + 1,
                ]);
            });
        } catch (\Throwable $e) {
            if ($documentDedupKey) {
                Cache::forget($documentDedupKey);
            }
            throw $e;
        }

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
                        'allow_exceed_time_limit' => 'required|boolean',
                        'reveal_answers' => 'required|boolean',
                        'max_attempts' => 'nullable|integer|min:1',
                        'available_from' => 'nullable|date', // Tambahkan validasi
                        'available_to' => 'nullable|date|after_or_equal:available_from',
                    ]);

                    // --- LOGIKA BARU DITAMBAHKAN DI SINI ---
                    if (!empty($validated['available_from'])) {
                        // 1. Ambil timezone instruktur (dari data user yang login)
                        $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');

                        // 2. Konversi waktu ke timezone instruktur
                        $availableFrom = Carbon::parse($validated['available_from'], $instructorTimezone)->utc();

                        // 3. Simpan waktu yang telah dikonversi
                        $validated['available_from'] = $availableFrom;
                    }

                    if (!empty($validated['available_to'])) {
                        // 1. Ambil timezone instruktur (dari data user yang login)
                        $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');

                        $availableTo = Carbon::parse($validated['available_to'], $instructorTimezone)->utc();
                        $validated['available_to'] = $availableTo;
                    }
                    // --- LOGIKA BARU DITAMBAHKAN DI SINI ---

                    $lessonable->update([
                        'title' => $validated['quiz_title'],
                        'description' => $validated['quiz_description'],
                        'pass_mark' => $validated['pass_mark'],
                        'time_limit' => $validated['time_limit'],
                        'allow_exceed_time_limit' => $validated['allow_exceed_time_limit'],
                        'reveal_answers' => $validated['reveal_answers'],
                        'max_attempts' => $validated['max_attempts'],
                        'available_from' => $validated['available_from'],
                        'available_to' => $validated['available_to'],

                    ]);
                    break;

                case 'lessonassignment':
                    $validated = $request->validate(['instructions' => 'required|string', 'due_date' => 'nullable|date', 'pass_mark' => 'required|integer|min:0|max:100',]);

                    // --- LOGIKA BARU DITAMBAHKAN DI SINI ---
                    if (!empty($validated['due_date'])) {
                        // 1. Ambil timezone instruktur
                        $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');

                        // 2. Parse input sebagai waktu lokal, lalu konversi ke UTC
                        $validated['due_date'] = Carbon::parse($validated['due_date'], $instructorTimezone)->utc();
                    }
                    // --- AKHIR LOGIKA BARU ---

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
                case 'lessonpoint': // Nama kelas setelah strtolower()
                    $validated = $request->validate([
                        'lessonpoin_title' => 'required|string|max:255',
                        'lessonpoin_description' => 'nullable|string',
                    ]);
                    $lessonable->update([
                        'title' => $validated['lessonpoin_title'],
                        'description' => $validated['lessonpoin_description'],
                    ]);
                    break;
                case 'lessonpolling':
                    $validated = $request->validate([
                        'polling_question' => 'required|string|max:255',
                        'polling_description' => 'nullable|string',
                        'polling_options' => 'required|array|min:2',
                        'polling_options.*' => 'required|string|max:255',
                        'is_active' => 'boolean',
                        'start_time' => 'nullable|date',
                        'end_time' => 'nullable|date|after_or_equal:start_time',
                        'show_voters' => 'boolean',
                        'show_results' => 'boolean',
                    ]);

                    $isActive = $request->has('is_active');
                    $showVoters = $request->has('show_voters');
                    $showResults = $request->has('show_results');
                    $startTime = null;
                    $endTime = null;

                    if (!empty($validated['start_time'])) {
                        $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');
                        $startTime = Carbon::parse($validated['start_time'], $instructorTimezone)->utc();
                    }
                    if (!empty($validated['end_time'])) {
                        $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');
                        $endTime = Carbon::parse($validated['end_time'], $instructorTimezone)->utc();
                    }

                    $lessonable->update([
                        'question' => $validated['polling_question'],
                        'description' => $validated['polling_description'],
                        'is_active' => $isActive,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'show_voters' => $showVoters,
                        'show_results' => $showResults,
                    ]);

                    $existingOptions = $lessonable->options()->orderBy('order')->pluck('text')->toArray();
                    $newOptions = $validated['polling_options'];
                    
                    if ($existingOptions !== $newOptions) {
                        $lessonable->options()->delete();
                        foreach ($newOptions as $index => $optionText) {
                            $lessonable->options()->create([
                                'text' => $optionText,
                                'order' => $index,
                            ]);
                        }
                    }
                    break;
                    case 'lessonwordcloud':
                        $validated = $request->validate([
                            'wordcloud_question' => 'required|string|max:255',
                            'wordcloud_description' => 'nullable|string',
                            'max_words' => 'required|integer|min:1|max:3',
                            'is_active' => 'boolean',
                            'start_time' => 'nullable|date',
                            'end_time' => 'nullable|date|after_or_equal:start_time',
                        ]);

                        $isActive = $request->has('is_active');
                        $startTime = null;
                        $endTime = null;

                        if (!empty($validated['start_time'])) {
                            $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');
                            $startTime = Carbon::parse($validated['start_time'], $instructorTimezone)->utc();
                        }
                        if (!empty($validated['end_time'])) {
                            $instructorTimezone = Auth::user()->timezone ?? config('app.timezone');
                            $endTime = Carbon::parse($validated['end_time'], $instructorTimezone)->utc();
                        }

                        $lessonable->update([
                            'question' => $validated['wordcloud_question'],
                            'description' => $validated['wordcloud_description'],
                            'max_words' => $validated['max_words'],
                            'is_active' => $isActive,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                        ]);
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
    public function pollingResults(Lesson $lesson)
    {
        $polling = $lesson->lessonable;
        
        if (get_class($polling) !== 'App\Models\LessonPolling') {
            abort(404);
        }

        $options = $polling->options()->with(['responses.user'])->withCount('responses')->get();
        $totalResponses = $polling->responses()->count();

        $chartLabels = $options->pluck('text')->toArray();
        $chartData = $options->pluck('responses_count')->toArray();

        return view('instructor.lessons.polling-results', compact('lesson', 'polling', 'options', 'totalResponses', 'chartLabels', 'chartData'));
    }

    public function wordcloudResults(Lesson $lesson)
    {
        $wordcloud = $lesson->lessonable;
        
        if (get_class($wordcloud) !== 'App\Models\LessonWordcloud') {
            abort(404);
        }

        // Get word frequencies
        $wordCounts = $wordcloud->responses()
            ->select('word', DB::raw('count(*) as count'))
            ->groupBy('word')
            ->orderByDesc('count')
            ->pluck('count', 'word')
            ->toArray();

        $totalResponses = $wordcloud->responses()->count();

        // Format for wordcloud2.js: [['word1', 12], ['word2', 8]]
        $wordCloudList = [];
        foreach ($wordCounts as $word => $count) {
            $wordCloudList[] = [$word, $count];
        }

        return view('instructor.lessons.wordcloud-results', compact('lesson', 'wordcloud', 'wordCounts', 'totalResponses', 'wordCloudList'));
    }

    public function togglePollingStatus(Request $request, Lesson $lesson)
    {
        $polling = $lesson->lessonable;
        
        if (get_class($polling) !== 'App\Models\LessonPolling') {
            abort(404);
        }

        $polling->is_active = !$polling->is_active;
        $polling->save();

        $status = $polling->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Polling berhasil {$status}.");
    }

    public function toggleWordcloudStatus(Request $request, Lesson $lesson)
    {
        $wordcloud = $lesson->lessonable;
        
        if (get_class($wordcloud) !== 'App\Models\LessonWordcloud') {
            abort(404);
        }

        $wordcloud->is_active = !$wordcloud->is_active;
        $wordcloud->save();

        $status = $wordcloud->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Word Cloud berhasil {$status}.");
    }
}

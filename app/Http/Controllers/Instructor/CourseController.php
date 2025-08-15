<?php

namespace App\Http\Controllers\Instructor;

use App\Models\Course;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\CourseCategory;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Auth::user()->courses()->with('category')->latest()->paginate(10);
        return view('instructor.courses.index', compact('courses'));
    }

    public function create()
    {
        $categories = CourseCategory::orderBy('name')->get();
        return view('instructor.courses.create', compact('categories'));
    }

    /**
     * Store a newly created course in the database.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:course_categories,id',
            'description' => 'nullable|string',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'availability_type' => ['required', Rule::in(['lifetime', 'period'])],
            'start_date' => 'required_if:availability_type,period|nullable|date',
            'end_date' => 'required_if:availability_type,period|nullable|date|after_or_equal:start_date',
            // DIUBAH: Validasi sekarang untuk 'money' atau 'diamonds'
            'payment_type' => ['required', Rule::in(['money', 'diamonds'])],
        ]);

        $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');

        Auth::user()->courses()->create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'thumbnail_url' => $thumbnailPath,
            'status' => 'draft',
            'availability_type' => $validated['availability_type'],
            'start_date' => $validated['availability_type'] === 'period' ? $validated['start_date'] : null,
            'end_date' => $validated['availability_type'] === 'period' ? $validated['end_date'] : null,
            'payment_type' => $validated['payment_type'],
            'price' => 0, // Harga akan di-set oleh admin
            'diamond_price' => 0, // Harga akan di-set oleh admin
        ]);

        return redirect()->route('instructor.courses.index')->with('success', 'Kursus berhasil dibuat.');
    }

    public function show(Course $course)
    {
        return view('instructor.courses.show', compact('course'));
    }

    public function edit(Course $course)
    {

        $categories = CourseCategory::orderBy('name')->get();
        return view('instructor.courses.edit', compact('course', 'categories'));
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, Course $course)
    {

        // Otorisasi: Pastikan instruktur hanya bisa meng-clone kursusnya sendiri
        if ($course->instructor_id != Auth::id()) {
            abort(403, 'Akses ditolak.');
        }



        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:course_categories,id',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'availability_type' => ['required', Rule::in(['lifetime', 'period'])],
            'start_date' => 'required_if:availability_type,period|nullable|date',
            'end_date' => 'required_if:availability_type,period|nullable|date|after_or_equal:start_date',
            // DIUBAH: Validasi sekarang untuk 'money' atau 'diamonds'
            'payment_type' => ['required', Rule::in(['money', 'diamonds'])],
        ]);

        $courseData = $validated;
        $courseData['slug'] = Str::slug($validated['title']);

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail_url) {
                Storage::disk('public')->delete($course->thumbnail_url);
            }
            $courseData['thumbnail_url'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        if ($course->payment_type !== $validated['payment_type']) {
            $courseData['price'] = 0;
            $courseData['diamond_price'] = 0;
        }

        $course->update($courseData);

        return redirect()->route('instructor.courses.index')->with('success', 'Kursus berhasil diperbarui.');
    }

    public function destroy(Course $course)
    {
        // Otorisasi: Pastikan instruktur hanya bisa meng-clone kursusnya sendiri
        if ($course->instructor_id != Auth::id()) {
            abort(403, 'Akses ditolak.');
        }


        if ($course->thumbnail_url) {
            Storage::disk('public')->delete($course->thumbnail_url);
        }
        $course->delete();
        return redirect()->route('instructor.courses.index')->with('success', 'Course deleted successfully.');
    }

    /**
     * Mengajukan kursus untuk direview oleh admin.
     */
    public function submitForReview(Course $course)
    {
        // Hanya bisa diajukan jika statusnya draft atau ditolak
        if (in_array($course->status, ['draft', 'rejected'])) {
            $course->status = 'pending_review';
            $course->save();
            return back()->with('success', 'Kursus berhasil diajukan untuk direview.');
        }
        return back()->with('error', 'Aksi tidak valid.');
    }

    /**
     * Mengubah status kursus menjadi privat.
     */
    public function makePrivate(Course $course)
    {
        // Hanya bisa dijadikan privat jika statusnya draft atau sudah dipublikasi
        if (in_array($course->status, ['draft', 'published'])) {
            $course->status = 'private';
            $course->save();
            return back()->with('success', 'Status kursus berhasil diubah menjadi privat.');
        }
        return back()->with('error', 'Aksi tidak valid.');
    }


    /**
     * Meng-clone sebuah kursus beserta seluruh modul dan pelajarannya.
     */
    public function clone(Course $course)
    {
        // Otorisasi: Pastikan instruktur hanya bisa meng-clone kursusnya sendiri
        if ($course->instructor_id != Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        try {
            DB::transaction(function () use ($course) {
                // 1. Replikasi data kursus utama
                $newCourse = $course->replicate();
                $newCourse->title = $course->title . ' (Salinan)';
                $newCourse->slug = Str::slug($newCourse->title);
                $newCourse->status = 'draft'; // Set status menjadi draft
                $newCourse->created_at = now();
                $newCourse->updated_at = now();
                $newCourse->push(); // Simpan kursus baru untuk mendapatkan ID

                // 2. Replikasi setiap modul
                foreach ($course->modules as $module) {
                    $newModule = $module->replicate();
                    $newModule->course_id = $newCourse->id;
                    $newModule->push(); // Simpan modul baru untuk mendapatkan ID

                    // 3. Replikasi setiap pelajaran di dalam modul
                    foreach ($module->lessons as $lesson) {
                        $lessonable = $lesson->lessonable; // Ambil konten spesifik (video, kuis, dll.)

                        // Replikasi konten spesifik terlebih dahulu
                        $newLessonable = $lessonable->replicate();

                        // Jika konten adalah kuis, kita juga perlu meng-clone relasi soalnya
                        if ($lesson->lessonable_type === 'App\Models\Quiz') {
                            $newLessonable->push(); // Simpan kuis baru untuk mendapatkan ID
                            $questionIds = $lessonable->questions()->pluck('questions.id');
                            $newLessonable->questions()->sync($questionIds);
                        } else {
                            $newLessonable->save();
                        }

                        // Replikasi data pelajaran utama
                        $newLesson = $lesson->replicate();
                        $newLesson->module_id = $newModule->id;

                        // Hubungkan dengan konten baru yang sudah direplikasi
                        $newLesson->lessonable_id = $newLessonable->id;
                        $newLesson->lessonable_type = $lesson->lessonable_type;
                        $newLesson->push();
                    }
                }
            });
        } catch (\Exception $e) {
            // Jika terjadi error, kembalikan dengan pesan error
            return redirect()->route('instructor.courses.index')->with('error', 'Terjadi kesalahan saat meng-clone kursus: ' . $e->getMessage());
        }

        return redirect()->route('instructor.courses.index')->with('success', 'Kursus berhasil di-clone.');
    }
}
